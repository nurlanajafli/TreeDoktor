#!/bin/bash

configure_aws() {
  logger "[POD] Configuring AWS";

  AWS_KEY=$(cat /opt/arbostar/aws/aws-id);
  AWS_SECRET=$(cat /opt/arbostar/aws/aws-secret);

  aws configure set aws_access_key_id $AWS_KEY && \
  aws configure set aws_secret_access_key $AWS_SECRET && \
  aws configure set default.region $REGION_ZONE;
  wait
}

update_code() {
  logger "[POD] Updating code";

  cd /app && git pull https://$GIT_TOKEN@github.com/$GIT_USER/arbostar-crm.git
  if [ $? -ne 0 ]
  then
    logger "[POD] Failed while pulling"; exit 1;
  fi
  wait
  php /app/index.php mixture opcache:clear;
  php /app/index.php mixture composer:dump-autoload;
  if [ $? -ne 0 ]
  then
    logger "[POD] Failed while clearing opcache"; exit 1;
  fi
  wait
}

process_file() {
  logger "[POD] Processing file: $1";
  deployment="/opt/arbostar/deployment/conf";
  filename=$1;
  if [ ! -f $filename ]; then
    logger "[POD] Failed to find $filename file to process" ; exit 1;
  fi
  if [ ! -f $deployment ]; then
    logger "[POD] Failed to find deployment file: $deployment"; exit 1;
  fi
  # copypasta from prev parser
  while IFS='=' read -r key value
  do
    if [[ $key != '#'* ]] && [[ $key != '' ]]; then
          OLDIFS=$IFS; IFS=$'\n'; for f in `grep -Frl "{{$key}}" $filename`; do sed -i 's|{{'$key'}}|'"$value"'|g' $f; done; IFS=$OLDIFS
    fi
  done < $deployment
  # clean up non defined vars
  sed -i '/{{.*}}/d' $filename
}

set_configs() {
  logger "[POD] Processing configs";

  # process company in place
  cp /app/application/config/company.deployment.php /app/application/config/company.tmp.php && \
  $(process_file "/app/application/config/company.tmp.php") || { logger '[POD] Failed to process file'; exit 1; }
  # get rid of the trailing line if exists
  if [ ! "$(tail -1 /app/application/config/company.tmp.php)" ]; then
    truncate -s -1 /app/application/config/company.tmp.php;
  fi
  mv /app/application/config/company.tmp.php /app/application/config/company.php;

  # process config in place
  cp /app/application/config/config.deployment.php /app/application/config/config.tmp.php && \
  $(process_file "/app/application/config/config.tmp.php") || { logger '[POD] Failed to process file'; exit 1; }
  mv /app/application/config/config.tmp.php /app/application/config/config.php;

  # process contants in place
  cp /app/application/config/constants.deployment.php /app/application/config/constants.tmp.php;
  AWS_KEY=$(cat /opt/arbostar/aws/aws-id);
  AWS_SECRET=$(cat /opt/arbostar/aws/aws-secret);
  sed -i 's|{{aws.access.key}}|'$AWS_KEY'|g' /app/application/config/constants.tmp.php;
  sed -i 's|{{aws.access.secret}}|'$AWS_SECRET'|g' /app/application/config/constants.tmp.php;
  $(process_file "/app/application/config/constants.tmp.php") || { logger '[POD] Failed to process file'; exit 1; }
  mv /app/application/config/constants.tmp.php /app/application/config/constants.php;

  # process database in place
  cp /app/application/config/database.deployment.php /app/application/config/database.tmp.php && \
  $(process_file "/app/application/config/database.tmp.php") || { logger '[POD] Failed to process file'; exit 1; }
  mv /app/application/config/database.tmp.php /app/application/config/database.php;

  # configure socket
  cp /app/socket/config.deployment.json /app/socket/config.tmp.json && \
  $(process_file "/app/socket/config.tmp.json") || { logger '[POD] Failed to process file'; exit 1; }
  mv /app/socket/config.tmp.json /app/socket/config.json

  # fiddle with time ( this may cause adverse effects )
  ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone;
  wait
}

set_crontab() {
  logger "[POD] Setting crontab";
  deployment="/opt/arbostar/deployment/conf";
  if [ ! -f $deployment ]; then
    logger "[POD] Failed to find deployment file: $deployment"; exit 1;
  fi
  crontab -r
  while IFS='=' read -r key value
  do
    if [[ "$key" != "#"* ]] && [[ "$key" != "" ]] && [[ "$key" == "crm.cron."* ]]; then
          task=$(cut -d'.' -f 3 <<< $key);
          (crontab -l 2>/dev/null; echo "$value /usr/local/bin/php /app/index.php cron $task 2>&1 | logger -t PROBLEMA";) | crontab -
    fi
    if [[ "$key" != "#"* ]] && [[ "$key" != "" ]] && [[ "$key" == "sys.cron."* ]]; then
          task=$(cut -d'.' -f 3 <<< $key);
          (crontab -l 2>/dev/null; echo "$value $task 2>&1 | logger -t PROBLEMA";) | crontab -
    fi
  done < $deployment
}

run_migrations() {
  logger "[POD] Running migrations";
  # run old migrations
  stout=$(php /app/index.php tools migrate);
  if [ $? -ne 0 ] && [[ "$stout" != "" ]]; then
    logger "[POD] Failed while running old migrations"; exit 1;
  fi
  wait

  # run new migrations
  stout=$(php /app/index.php mixture migrate --force);
  if [ $? -ne 0 ] && [[ "$stout" != "" ]]; then
    logger "[POD] Failed while running new migrations"; exit 1;
  fi
  wait
}

start_services() {
  logger "[POD] Starting services";
  supervisorctl start nginxd node queue;
  if [ $? -ne 0 ]; then
    logger "[POD] could not start"; exit 1;
  fi
}

restart_services() {
  logger "[POD] Restarting services";
  supervisorctl restart nginxd node queue;
  if [ $? -ne 0 ]; then
    logger "[POD] could not restart"; exit 1;
  fi
}

stop_services() {
  logger "[POD] Stopping services";
  supervisorctl stop nginxd node queue;
  if [ $? -ne 0 ]; then
    logger "[POD] could not stop"; exit 1;
  fi
}

update() {
  logger "[POD] Received Update command!";
  # update code
  update_code
  # run migrations
  run_migrations
  # REPORT TO SNS
  logger "Finished updating "$CLIENT_ID" pod";
}

patch() {
  logger "[POD] Received Patch command!";
  # get and install configs
  $(set_configs) || { logger '[POD Patch] Failed while auto-configuring'; exit 1; }
  # check for crontab
  $(set_crontab) || { logger '[POD Patch] Failed while defining crontab'; exit 1; }
  # restart services
  restart_services
  # REPORT TO SLACK
  curl -X POST -H 'Content-type: application/json' --data '{"text":"Finished updating '$CLIENT_ID' pod"}' $SLACK_URL
  logger "Finished patching "$CLIENT_ID" pod";
}

init() {
  logger "[POD] Enterred Init!";
  # set up client aws credentials
  $(configure_aws) || { logger '[POD Init] Failed to configure aws credentials'; exit 1; }
  # get and install configs
  $(set_configs) || { logger '[POD Init] Failed while auto-configuring'; exit 1; }
  # check for crontab
  $(set_crontab) || { logger '[POD Init] Failed while defining crontab'; exit 1; }
  # 10. healthcheck
  touch /app/healthcheck.html;
  # run migrations
  run_migrations
  # start services
  start_services
  # start watcher
  supervisorctl start watch
  wait
  # REPORT TO SLACK
  curl -X POST -H 'Content-type: application/json' --data '{"text":"Finished configuring '$CLIENT_ID' pod"}' $SLACK_URL
  logger "Finished configuring "$CLIENT_ID" pod";
}

$1

wait
# auf wiedersehen
logger "[POD] Up and Atom!";

exit 0;

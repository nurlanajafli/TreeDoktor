# BUILD FOR DEPLOY TO K8S
# build core or pull from dockerhub/aws or re-build
  core_tag=1.1;
  docker build -f Dockerfile.core -t arbostar-core:$core_tag .

  # OR pull from aws
  region=us-west-2
  aws configure set region $region
  aws ecr get-login-password --region $region | docker login --username AWS --password-stdin 411650532144.dkr.ecr.$region.amazonaws.com
  docker pull 411650532144.dkr.ecr.$region.amazonaws.com/arbostar-core:$core_tag
  docker tag 411650532144.dkr.ecr.$region.amazonaws.com/arbostar-core:$core_tag arbostar-core:$core_tag

  # OR pull from dockerhub
  docker pull identifier/arbostar-core:$core_tag
  docker tag identifier/arbostar-core:$core_tag arbostar-core:$core_tag

  # optional - send to aws after build
  region=us-west-2
  aws configure set region $region
  aws ecr get-login-password --region $region | docker login --username AWS --password-stdin 411650532144.dkr.ecr.$region.amazonaws.com
  docker tag arbostar-core:$core_tag 411650532144.dkr.ecr.$region.amazonaws.com/arbostar-core:$core_tag
  docker push 411650532144.dkr.ecr.$region.amazonaws.com/arbostar-core:$core_tag

  # or send to dockerhub
  docker login
  docker tag arbostar-core:$core_tag identifier/arbostar-core:$core_tag
  docker push identifier/arbostar-core:$core_tag

#  build arbostar docker image for k8s or k8s-proxy
  core_tag=1.1;

  git_hash=$(git rev-parse --short HEAD) && echo $git_hash;
  docker build -f Dockerfile.wrap -t arbostar:$git_hash . --build-arg IMAGETAG=arbostar-core:$core_tag --build-arg CONTEXT=k8s
#  OR
  git_hash=$(git rev-parse --short HEAD) && echo $git_hash;
  docker build -f Dockerfile.wrap -t arbostar:$git_hash . --build-arg IMAGETAG=arbostar-core:$core_tag --build-arg CONTEXT=k8s-proxy


  # upload to aws
  region=us-west-2
  aws configure set region $region
  aws ecr get-login-password --region $region | docker login --username AWS --password-stdin 411650532144.dkr.ecr.$region.amazonaws.com
  docker tag arbostar:$git_hash 411650532144.dkr.ecr.$region.amazonaws.com/arbostar:$git_hash
  docker push 411650532144.dkr.ecr.$region.amazonaws.com/arbostar:$git_hash

  # upload to dockerhub
  docker login
  docker tag arbostar:$git_hash identifier/arbostar:$git_hash
  docker push identifier/arbostar:$git_hash



# DEPLOY LOCAL
# have arbostar-core:$core_tag already pre-built by ether downloading from dockerhub/aws or building own
# dockerhub, make sure you pulling working core
  docker pull identifier/arbostar-core:1.1

# 1. build local from core (assuming tag 0)
  core_tag=1.1

  # copy and edit deployment config
  cp deployments/local/conf.blank to deployments/local/conf

  # build the docker image
  docker build -f Dockerfile.wrap -t arbostar:local --build-arg IMAGETAG=arbostar-core:$core_tag --build-arg DEPLOYMENT_PATH=deployments/local .

# 2. run the stack
  cp Dockerstack.blank.yaml Dockerstack.yaml

  # edit GIT USER AND SECRET
  AWS keys see in deployments/local/conf

  cat Dockerstack.yaml | docker stack deploy --compose-file - arbostar

# 3. destroy the stack when no longer needed
    docker stack rm arbostar


# run as Dockers

docker network create arbonet

docker run -p 33060:3306 \
--env MYSQL_DATABASE=exampledb \
--env MYSQL_USER=exampleuser \
--env MYSQL_PASSWORD=examplepass \
--env MYSQL_RANDOM_ROOT_PASSWORD=0 \
--env MYSQL_ROOT_PASSWORD=banana \
--network=arbonet \
-v arbostar_db:/var/lib/mysql-maria \
 mariadb:10.6

docker run -p 80:80 -p 443:443 -p 8895:8895 \
--env CI_ENV=development \
--env CLIENT_ID=pilot \
--env REGION_ZONE=us-west-2 \
--env CODEBASE=release \
--env TZ=America/Toronto \
--env PHP_DATE_TIMEZONE=America/Toronto \
--env WEB_ALIAS_DOMAIN=localhost \
--env PHP_DISPLAY_ERRORS=0 \
--env PHP_MEMORY_LIMIT="256M" \
--env PHP_MAX_EXECUTION_TIME="300" \
--env PHP_POST_MAX_SIZE="100M" \
--env PHP_UPLOAD_MAX_FILESIZE="100M" \
--env php.opcache.validate_timestamps="on" \
--env fpm.global.emergency_restart_threshold="10" \
--env fpm.global.emergency_restart_interval="1m" \
--env fpm.global.process_control_timeout="10s" \
--env FPM_PM_MAX_CHILDREN="4" \
--env FPM_PM_START_SERVERS="2" \
--env FPM_PM_MIN_SPARE_SERVERS="1" \
--env FPM_PM_MAX_SPARE_SERVERS="2" \
--env FPM_MAX_REQUESTS="500" \
--env GIT_USER=<GIT_USER> \
--env GIT_TOKEN=<GIT_TOKEN> \
--env AWS_ID=<AWS_ID> \
--env AWS_TOKEN=<AWS_TOKEN> \
--network=arbonet \
-v /Users/regex/Dev/arbostar/crm/deployments/local/conf:/opt/arbostar/deployment/conf \
-v /Users/regex/Dev/arbostar/crm/deployments/local/ssl/server.crt:/etc/node-ssl/server.crt \
-v /Users/regex/Dev/arbostar/crm/deployments/local/ssl/server.key:/etc/node-ssl/server.key \
-v /Users/regex/Dev/arbostar/crm/deployments/local/ssl/server.crt:/opt/docker/etc/nginx/ssl/server.crt \
-v /Users/regex/Dev/arbostar/crm/deployments/local/ssl/server.key:/opt/docker/etc/nginx/ssl/server.key \
arbostar:local

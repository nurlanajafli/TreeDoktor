#!/bin/bash

exit_message=$1
process_name=$2
ignored_services="init"

for var in "$ignored_services"
do
  if [[ "$var" = "$process_name" ]]; then
    exit 0;
  fi
done

case $exit_message in
  "PROCESS_STATE_STOPPING" )
    exit 0;
    ;;
  "PROCESS_STATE_EXITED" )
    curl -X POST -H 'Content-type: application/json' --data "{'text':'PANIC! $CLIENT_ID $process_name $exit_message'}" $SLACK_URL
    ;;
  "PROCESS_STATE_FATAL" )
    curl -X POST -H 'Content-type: application/json' --data "{'text':'PANIC! $CLIENT_ID $process_name $exit_message'}" $SLACK_URL
    ;;
esac

exit 0;

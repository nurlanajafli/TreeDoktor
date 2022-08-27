#!/bin/bash

inotifywait -q -m /opt/arbostar/deployment/ |
while read -r filename event; do
  if [[ "$event" == "MOVED_TO ..data" ]]; then
    /opt/arbostar/pod.sh patch;
  fi
done

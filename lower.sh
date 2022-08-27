#!/bin/bash

files="$(find ./application/controllers ./application/models ./application/modules/*/controllers -type f -name "*.php")";
for file in $files
do
    name="$(basename $file)";
    dir="$(dirname $file)"/;
    lowercase="^[A-Z]"
    if [[ $name =~ $lowercase ]]
    then
        class="$(echo $name | cut -d'.' -f1)"
        lowered="$(echo $class| awk '{print tolower(substr($0,1,1)) substr($0,2)}')"
        git mv $dir$class.php $dir$lowered.php
        echo $dir$name $lowered;
    fi
done
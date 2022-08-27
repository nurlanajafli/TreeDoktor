#!/bin/bash

files="$(find ./application/controllers ./application/models ./application/modules/*/controllers -type f -name "*.php")";
for file in $files
do
    name="$(basename $file)";
    dir="$(dirname $file)"/;
    lowercase="^[a-z]"
    if [[ $name =~ $lowercase ]]
    then
        class="$(echo $name | cut -d'.' -f1)"
        capitalized="$(echo $class| awk '{print toupper(substr($0,1,1)) substr($0,2)}')"
        git mv $dir$class.php $dir$capitalized.php
        sed -i .old -e "s/^class $class/class $capitalized/" $dir$capitalized.php
        echo $dir$name $capitalized;
    fi
done

files="$(find ./application/controllers ./application/models ./application/modules/*/controllers -type f -name "*.old")";
for file in $files
do
    rm $file
done

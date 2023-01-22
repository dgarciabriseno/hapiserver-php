#!/bin/bash
target_test=""
if [ ! -z $1 ]
then
	target_test="--filter $1"
fi

../vendor/bin/phpunit $target_test --testdox tests

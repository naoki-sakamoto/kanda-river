#!/bin/bash
CURRENTDIR=`dirname $0`
#PHPUNIT=$CURRENTDIR/../vendor/bin/phpunit
PHPUNIT=$CURRENTDIR/../vendor/phpunit/phpunit/phpunit

if [ "a$1" == "a" ] ; then
  echo "USAGE: test.sh TESTFILE [TESTNAME]"
  exit 1
fi
testfile=$1

testname=""
if [ "a$2" != "a" ] ; then testname=$2 ; fi

if [ "a$testname" != "a" ] ; then
  php $PHPUNIT --debug --colors --stderr --filter "$testname" $testfile
else
  php $PHPUNIT --debug --colors --stderr $testfile
fi
ret=$?
exit $ret
#!/bin/bash
CURRENTDIR=`dirname $0`
BASEDIR=../$CURRENTDIR
CONFIGDIR=$BASEDIR/config
TESTDIR=$BASEDIR/test
TARGETDIR=$TESTDIR/mods
TMPFILE=`mktemp temp.XXXXXX`

echo 0 0 > $TMPFILE

echo "--- Start models test ---"
find $TARGETDIR -name "test_*.php" | while read testfile ; do
  count=`cat $TMPFILE | awk '{print $1}'`
  success=`cat $TMPFILE | awk '{print $2}'`
  echo "[$testfile]"
  $TESTDIR/test.sh $testfile
  if [ $? -eq 0 ] ; then success=$(($success + 1)) ; fi
  count=$(($count + 1))
  echo $count $success > $TMPFILE
  echo "----------------------------------------"
done

count=`cat $TMPFILE | awk '{print $1}'`
success=`cat $TMPFILE | awk '{print $2}'`
rm -f $TMPFILE
failure=$((count - success))
echo "Class: $count, Success: $success, Failure: $failure."
echo "--- End ---"
if [ $failure -gt 0 ] ; then
  exit 1
fi
exit 0

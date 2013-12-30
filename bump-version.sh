#!/bin/sh

ACTUAL='0\.0\.7'
FUTURE='0.0.8'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


#!/bin/sh

ACTUAL='0\.0\.13'
FUTURE='0.0.14'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


#!/bin/sh

ACTUAL='0\.0\.6'
FUTURE='0.0.7'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


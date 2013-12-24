#!/bin/sh

ACTUAL='0\.0\.5'
FUTURE='0.0.6'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


#!/bin/sh

ACTUAL='0\.0\.12'
FUTURE='0.0.13'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


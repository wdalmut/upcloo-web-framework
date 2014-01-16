#!/bin/sh

ACTUAL='0\.0\.9'
FUTURE='0.0.10'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


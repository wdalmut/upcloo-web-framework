#!/bin/sh

ACTUAL='0\.0\.11'
FUTURE='0.0.12'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


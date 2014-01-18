#!/bin/sh

ACTUAL='0\.0\.10'
FUTURE='0.0.11'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


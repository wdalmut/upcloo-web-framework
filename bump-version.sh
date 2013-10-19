#!/bin/sh

ACTUAL='0\.0\.3'
FUTURE='0.0.4'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


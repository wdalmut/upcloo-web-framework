#!/bin/sh

ACTUAL='0\.0\.4'
FUTURE='0.0.5'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


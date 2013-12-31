#!/bin/sh

ACTUAL='0\.0\.8'
FUTURE='0.0.9'

sed -i "s/${ACTUAL}/${FUTURE}/" docs/conf.py


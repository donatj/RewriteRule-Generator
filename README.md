# Mod Rewrite Rule Generator

[![Build Status](https://travis-ci.org/donatj/Mod-Rewrite-Rule-Generator.svg?branch=master)](https://travis-ci.org/donatj/Mod-Rewrite-Rule-Generator)

Web Frontend: https://donatstudios.com/RewriteRule_Generator

## What it is

* A simple builder of RewriteCond / RewriteRule's handling GET strings in any order.
* Free and open source

## What it is not

* Perfect

## Todo:

* [ ] Nginx Option
	* This is proving to be more difficult than initially anticipated. Handling the GET parameters in **any order** may require the use of
	if statements which is frowned upon in the Nginx community and has performance overheads.

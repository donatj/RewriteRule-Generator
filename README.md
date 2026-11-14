# Mod Rewrite Rule Generator

![CI](https://github.com/donatj/RewriteRule-Generator/workflows/CI/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/donatj/rewrite-generator/v/stable)](https://packagist.org/packages/donatj/rewrite-generator)
[![License](https://poser.pugx.org/donatj/rewrite-generator/license)](https://packagist.org/packages/donatj/rewrite-generator)

Web Frontend: https://donatstudios.com/RewriteRule_Generator

The Caddy option is a prototype. Place its output inside a Caddy site block.
It accepts simple paths and query parameters. It reports unsupported URLs rather than generating a rule for them.
For example, encoded paths and query values, repeated query keys, ports, and fragments are not supported yet.
Caddy parses query parameters and normalizes paths, so its matching can differ from Apache.
Generated matcher names must be unique within the site block.

## What it is

* A simple builder of RewriteCond / RewriteRule's handling GET strings in any order.
* Free and open source

## What it is not

* Perfect

## Todo:

* [ ] Nginx Option
	* This is proving to be more difficult than initially anticipated. Handling the GET parameters in **any order** may require the use of
	if statements which is frowned upon in the Nginx community and has performance overheads.

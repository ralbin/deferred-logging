# deferred-logging

Magento Open source and Adobe Commerce deferred logging using RabbitMQ 

Why is the namespace Mac.  Well, it basically was decided out of frustration.  
I had no idea what I wanted.  
Then it stuck me, let's mash up Magento and Adobe Commerce.  
M = Magento and ac = Adobe Commerce

This module is meant to help with the issue of needing logging but dancing around how much and when to use it.
When I am reviewing a module for performance issues, one area look at is how often are we writing to logs.
This got me to thinking, what if we just deffered the process of writing to the logs, them my justification for calling
this out for being a potential performance impacting event when heavily used is negated.  

This is completely open source and meant for community use and contribution. 

### If you are going to contribute, please be aware that I will be looking for a few basic things
* You must use the Magento Coding standard and using the CLI or PHPStorm plugin will help
* You must follow the PSR standards.  
  We still sometimes have to dance around a few things due to legacy core expectations, 
  but the expectation is we will when appropriate
* Please load test and let me know how things change.
  Feedback is needed.  Anything you can provide will be greatly appreciated
* Do not get irritated with PR review feedback.  When possible I try to refer to a DevDoc, PSR standard 
  or other official documentation for why something may need to be revised.  Healthy debate is welcome as long as it's
  professional and clean.

Thank you for checking out this repo and hopefully it helps

### To install
@TODO provide nice easy instructions

### To Uninstall
@TODO provide even easier instructions


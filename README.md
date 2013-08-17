# AMBERSTATS

## Introduction

AmberStats is a lightweight application for phoning home from open source
applications to track install base and the environment used for adoptions
by end users.

This information allows the application developer to know what platforms to
target, what versions are out there and how quickly new versions are adopted.

It consists of two parts:

1. Lightweight API used to collecting notifications from applications using
   the phone home code (such as any application built around Amberphplib).

2. Simple web-based interface for displaying the statistics of the applications
   that are reported back.


## Information Collected

Systems that phone home with information can be a touchy subject - AmberStats'
phone home API is documented and includes only the following information:

Field                 Example
-----                 -----
Application Name      'Amberdms Billing System'
Application Version   '1.3.0'
Server App            'Apache/2.0.52 (CentOS)'
Language Version      '5.1.6'
Subscription Type     'opensource'
Subscription ID       'qwertyuiop234567890'

It is recommended that you have phone home off by default and make it opt-in
for users along with clear details of what information is sent and what you are
using it for.

The only identifying information collected is the subscription ID (randomly
generated, used to tell different installations apart, in order to track their
upgrade lifespan) and the IP address of the server sending the API request.



## Getting Started

To get started with AmberStats, you'll need to setup the AmberStats collection
server and UI, then start adding it to applications.

Refer to https://projects.jethrocarr.com/p/oss-amberstats/doc/ for details on
how to setup AmberdStats


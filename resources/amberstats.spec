Summary: A web-based management system for DNS, consisting of a PHP web interface and some PHP CLI components to hook into FreeRadius.
Name: amberstats
Version: 0.0.1
Release: 1%{dist}
License: AGPLv3
URL: http://projects.jethrocarr.com/p/oss-amberstats/
Group: Applications/Internet
Source0: amberstats-%{version}.tar.bz2

BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch: noarch
BuildRequires: gettext

Requires: httpd, mod_ssl
Requires: php >= 5.3.0, mysql-server, php-mysql, php-ldap, php-soap
Requires: perl, perl-DBD-MySQL
Prereq: httpd, php, mysql-server, php-mysql


%description
Amberstats is an application for collecting and reporting on phone home statistics
from remote applications, such as software distributed by a developer.

%prep
%setup -q -n amberstats-%{version}

%build


%install
rm -rf $RPM_BUILD_ROOT
mkdir -p -m0755 $RPM_BUILD_ROOT%{_sysconfdir}/amberstats/
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/amberstats/

# install application files and resources
cp -pr * $RPM_BUILD_ROOT%{_datadir}/amberstats/


# install configuration file
install -m0700 htdocs/include/sample-config.php $RPM_BUILD_ROOT%{_sysconfdir}/amberstats/config.php
ln -s %{_sysconfdir}/amberstats/config.php $RPM_BUILD_ROOT%{_datadir}/amberstats/htdocs/include/config-settings.php

# install linking config file
install -m755 htdocs/include/config.php $RPM_BUILD_ROOT%{_datadir}/amberstats/htdocs/include/config.php

# install the apache configuration file
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d
install -m 644 resources/amberstats-httpdconfig.conf $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/amberstats.conf

# install the cronfile
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/
install -m 644 resources/amberstats.cron $RPM_BUILD_ROOT%{_sysconfdir}/cron.d/amberstat


%post

# Reload apache
echo "Reloading httpd..."
/etc/init.d/httpd reload

# update/install the MySQL DB
if [ $1 == 1 ];
then
	# install - requires manual user MySQL setup
	echo "Run cd %{_datadir}/amberstats/resources/; ./autoinstall.pl to install the SQL database."
else
	# upgrade - we can do it all automatically! :-)
	echo "Automatically upgrading the MySQL database..."
	%{_datadir}/amberstats/resources/schema_update.pl --schema=%{_datadir}/amberstats/sql/ -v
fi


%postun www

# check if this is being removed for good, or just so that an
# upgrade can install.
if [ $1 == 0 ];
then
	# user needs to remove DB
	echo "amberstats has been removed, but the MySQL database and user will need to be removed manually."
fi


%preun bind

# stop running process
/etc/init.d/amberstats_logpush stop



%clean
rm -rf $RPM_BUILD_ROOT

%files www
%defattr(-,root,root)
%config %dir %{_sysconfdir}/amberstats
%attr(770,root,apache) %config(noreplace) %{_sysconfdir}/amberstats/config.php
%attr(660,root,apache) %config(noreplace) %{_sysconfdir}/httpd/conf.d/amberstats.conf
%{_datadir}/amberstats/htdocs
%{_datadir}/amberstats/resources
%{_datadir}/amberstats/sql

%doc %{_datadir}/amberstats/README.md
%doc %{_datadir}/amberstats/docs/AUTHORS
%doc %{_datadir}/amberstats/docs/CONTRIBUTORS
%doc %{_datadir}/amberstats/docs/COPYING


%files bind
%defattr(-,root,root)
%config %dir %{_sysconfdir}/amberstats
%config %dir %{_sysconfdir}/cron.d/amberstats-bind
%config(noreplace) %{_sysconfdir}/named.amberstats.conf
%config(noreplace) %{_sysconfdir}/amberstats/config-bind.php
%{_datadir}/amberstats/bind
/etc/init.d/amberstats_logpush


%changelog
* Sat Aug 17 2013 Jethro Carr <jethro.carr@amberdms.com> 0.0.1
- Pre-release Version

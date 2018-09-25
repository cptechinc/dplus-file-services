### Setup Hosts file to handle the subdomains
127.0.0.1       salesportal.stat-technologies.com
17.0.0.1        www.salesportal.stat-technologies.com

### Install wkhtmltopdf and its dependencies
yum install xorg-x11-fonts-75dpi.noarch
yum install xorg-x11-fonts-Type1.noarch
yum install xorg-x11-server-Xvfb
rpm -ivh https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.2/wkhtmltox-0.12.2_linux-centos6-amd64.rpm

### TEST 
wkhtmltopdf 'http://salesportal.stat-technologies.com/print/order/?ordn=0001201700&referenceID=l4u10o27ia7oqpj3j6t2a0mfi5&print=true' stuff.pdf

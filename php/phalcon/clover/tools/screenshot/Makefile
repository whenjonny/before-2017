run:
	if [ -a /usr/bin/node ]; then \
		echo 'node exist'; \
	else \
		unzip install/node.zip -d install/ ; \
		ln -s install/node/bin/node /usr/bin/ ; \
		ln -s install/node/bin/npm /usr/bin/ ; \
	fi
	if [ -a /usr/bin/phantomjs ]; then \
		echo 'phantomjs exist'; \
	else \
		unzip install/phantomjs.zip -d install/  ; \
		ln -s install/phantomjs/bin/phantomjs /usr/bin/ ; \
	fi
	if [ -d ../../public/images/snapshot ]; then \
		echo 'snapshot exist'; \
	else \
		mkdir ../../public/images/snapshot; \
		ln -s ../../public/images/snapshot snapshot; \
	fi
	nohup node snapshot.js &

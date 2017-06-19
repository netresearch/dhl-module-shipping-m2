FROM registry.netresearch.de/magento-ci/base:%app-version%
MAINTAINER Christoph Aßmann <christoph.assmann@netresearch.de>

RUN wget https://raw.githubusercontent.com/netresearch/retry/master/retry -O /usr/local/bin/retry && \
    chmod +x /usr/local/bin/retry

VOLUME ["/var/www/magetwo.nrdev.de"]

COPY run.sh /run.sh
RUN chmod u+rwx /run.sh

WORKDIR /var/www/magetwo.nrdev.de

CMD ["mysql","3306"]
ENTRYPOINT ["/run.sh"]

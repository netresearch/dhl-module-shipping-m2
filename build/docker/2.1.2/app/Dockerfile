FROM registry.netresearch.de/magento-ci/base:2.1.2
MAINTAINER Christoph Aßmann <christoph.assmann@netresearch.de>

RUN wget https://raw.githubusercontent.com/netresearch/retry/master/retry -O /usr/local/bin/retry && \
    chmod +x /usr/local/bin/retry

VOLUME ["/var/www/magetwo.nrdev.de"]

COPY run.sh /run.sh
RUN chmod u+rwx /run.sh

WORKDIR /var/www/magetwo.nrdev.de

CMD ["mysql","3307"]
ENTRYPOINT ["/run.sh"]

container_name := dependency-visualizer-container
image_name := dependency-visualizer-image

host_directory := $(shell pwd)
container_directory := /usr/local/dependency-visualizer

image_exists := $(shell docker images --filter "reference=$(image_name)" --format "{{.Repository}}")
container_exists := $(shell docker ps -a --filter "name=$(container_name)" --format "{{.Names}}")

.PHONY: build
build:
ifneq ($(image_exists),$(image_name))
	docker build -t $(image_name) $(host_directory)
endif

.PHONY: composer-install
composer-install:
ifeq ($(wildcard $(host_directory)/vendor),)
	docker run --rm -v "$(host_directory):$(container_directory)" "$(image_name)" composer install
endif

.PHONY: run
run: build composer-install
ifeq ($(container_exists),$(container_name))
	docker start -i -a "$(container_name)"
else
	docker run -it --name "$(container_name)" -v "$(host_directory):$(container_directory)" "$(image_name)"
endif

.PHONY: clean
clean:
ifneq ($(container_exists),)
	docker rm -f $(container_name)
endif
ifneq ($(image_exists),)
	docker rmi $(image_name)
endif
	sudo rm -fr vendor

.DEFAULT_GOAL := run

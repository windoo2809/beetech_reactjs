# base image
FROM node:12.16.1-alpine
#FROM alpine-node:12.16.1
# FROM buildpack-deps:stretch

# RUN groupadd --gid 1000 node \
#   && useradd --uid 1000 --gid node --shell /bin/bash --create-home node

# ENV NODE_VERSION 12.16.1


# set working directory
WORKDIR /app

# add `/app/node_modules/.bin` to $PATH
ENV PATH /app/node_modules/.bin:$PATH

# install and cache app dependencies
COPY package.json /app/package.json
COPY package-lock.json /app/package-lock.json
# COPY yarn.lock /app/yarn.lock
RUN npm ci 
RUN npm install
RUN ls -la /app/node_modules
CMD ["npm", "start"]
# CMD ["npm", "run", "start:dev"]
#CMD ["npm", "run", "start:staging"]

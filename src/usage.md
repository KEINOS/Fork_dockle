# Dockle Online

This is a Web API that gets the analysis result of Dockle in JSON format for Shields.IO endpoint badge.

The first request is slow to responce in order to analyze. From then on, it returns the cached result.

## Basic Usage

Specify your image name as below and you will get the results in JSON for your Shields.IO powered badge.

  %HOST%/[IMAGE NAME]<[QUERY OPTION]>

  IMAGE NAME: Image name in Docker Hub.
    - Ex:
      - alpine
      - alpine:3.9.4
      - keinos/alpine
      - keinos/alpine:latest
      - keinos/alpine:v3.9.3

  QUERY OPTION: Option to update cache or display the error details.
    - ?update
          Re-analyses the image and updates the results.
        - Ex: %HOST%/keinos/alpine:latest?update
    - ?update&ignore[]=
    - ignore[]=
          Specify the checkpoint to ignore. Repeatable. Only works when chache.
        - Ex: %HOST%/keinos/alpine?update&ignore[]=CIS-DI-0001&ignore[]=CIS-DI-0005
        - Ex: %HOST%/keinos/alpine?ignore[]=CIS-DI-0001&ignore[]=CIS-DI-0005
    - ?details
          Displays the details of the analysis. (Not in Shields.IO format)
        - Ex: %HOST%/keinos/alpine:v3.9.3?details

## Info

- Dockle
    The Docker Container Image Linter for Security.
  - https://github.com/goodwithtech/dockle

- Shields.IO
    Quality metadata badges for Open Source.
  - https://shields.io/endpoint

## Issues

- https://github.com/KEINOS/Fork_dockle/issues

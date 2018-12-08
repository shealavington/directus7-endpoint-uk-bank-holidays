#  Directus 7 — Extensions 
Hi! I’m Shea, feel free to use these extensions for whatever you need!

[Directus 7](https://github.com/directus) is a Headless CMS, it's built so that it can be extended easily. All extensions that I build for Directus, will be made open-source here. 

Every extension will have it's own directory, to "install" these extensions, add the contents of the extension directory to the `/extensions/custom` directory inside your Directus API.

## Endpoints 
> **Note:** _After installing, endpoints can be used by visiting: https://`install_location`/public/`project`/custom/`endpoint`/`options`_

###  [#](#bank-holidays) Bank Holidays

*This extension puts a wrapper around the UK Government list of Bank Holidays that allows you to query and only retrieve the data that you need using Directus endpoints.* 

| Options | Description |
|---|---|
| / | By visiting the endpoint without any parameters, you can retrieve a full list of bank-holidays for all divisions.  |
| /`division` | There are three divisions, `northern-ireland`, `england-and-wales`, `scotland`, you can query any of these three as your primary query source. |
| /`division`/next/`amount` | By requesting an amount of `1`, an object will be returned, otherwise an array will be returned. |
| /`division`/last/`amount` | By requesting an amount of `1`, an object will be returned, otherwise an array will be returned. |
| /`division`/check/`year`/`month`/`date` | Filter events by `year`, `month`, or check a specific `date`. By checking a full date, an object will be returned, the rest will return an array. |
-- Agregar columna para TRA
ALTER TABLE `emisores` 
ADD COLUMN `afip_tra` longtext DEFAULT NULL AFTER `afip_passphrase`;

-- Insertar TRA de producción inicial
UPDATE `emisores` SET 
  `afip_tra` = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<loginTicketResponse version="1.0">
    <header>
        <source>CN=wsaahomo, O=AFIP, C=AR, SERIALNUMBER=CUIT 33693450239</source>
        <destination>SERIALNUMBER=CUIT 20251202703, CN=lurhom</destination>
        <uniqueId>1433832239</uniqueId>
        <generationTime>2025-09-24T13:41:33.906-03:00</generationTime>
        <expirationTime>2025-09-25T01:41:33.906-03:00</expirationTime>
    </header>
    <credentials>
        <token>PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pgo8c3NvIHZlcnNpb249IjIuMCI+CiAgICA8aWQgc3JjPSJDTj13c2FhaG9tbywgTz1BRklQLCBDPUFSLCBTRVJJQUxOVU1CRVI9Q1VJVCAzMzY5MzQ1MDIzOSIgZHN0PSJDTj13c2ZlLCBPPUFGSVAsIEM9QVIiIHVuaXF1ZV9pZD0iNDYxMjM3MTU0IiBnZW5fdGltZT0iMTc1ODczMjAzMyIgZXhwX3RpbWU9IjE3NTg3NzUyOTMiLz4KICAgIDxvcGVyYXRpb24gdHlwZT0ibG9naW4iIHZhbHVlPSJncmFudGVkIj4KICAgICAgICA8bG9naW4gZW50aXR5PSIzMzY5MzQ1MDIzOSIgc2VydmljZT0id3NmZSIgdWlkPSJTRVJJQUxOVU1CRVI9Q1VJVCAyMDI1MTIwMjcwMywgQ049bHVyaG9tIiBhdXRobWV0aG9kPSJjbXMiIHJlZ21ldGhvZD0iMjIiPgogICAgICAgICAgICA8cmVsYXRpb25zPgogICAgICAgICAgICAgICAgPHJlbGF0aW9uIGtleT0iMjAyNTEyMDI3MDMiIHJlbHR5cGU9IjQiLz4KICAgICAgICAgICAgICAgIDxyZWxhdGlvbiBrZXk9IjMzNzE2MjgyODE5IiByZWx0eXBlPSI0Ii8+CiAgICAgICAgICAgIDwvcmVsYXRpb25zPgogICAgICAgIDwvbG9naW4+CiAgICA8L29wZXJhdGlvbj4KPC9zc28+Cg==</token>
        <sign>OlH2lKZF0W8B57gMmqV4SO/Ub6g+WIQK2Eo6W+TnEpHutDCpnCnE/e1pmqL0+5mBnhHLPyF1ALbvK5ns6IM2Fb0X9d3ctuY3QO8IHCv+SdoI5+AQy+RbcQuHhiVmPvDeuLj7lq74mdqiQKZaJDAMq6mHVb9oDMkCBPZGr5dufjE=</sign>
    </credentials>
</loginTicketResponse>'
WHERE `id` = 1;

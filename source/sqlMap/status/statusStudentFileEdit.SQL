SELECT
RTRIM(INFOTYPE) AS INFOTYPE,
RTRIM(CONVERT(varchar(10),DATE,20)) as DATE,

RTRIM(FILENO) AS FILENO,RTRIM(REM) AS REM,
RTRIM(STUDENTNO) AS STUDENTNO from REGISTRIES
WHERE RECNO=:RECNO
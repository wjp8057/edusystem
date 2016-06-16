SELECT DISTINCT RTRIM(WHO) AS WHO,
                  CLASSES.CLASSNAME AS WHOSNAME,
                  TYPE,
                  MON,
                  TUE,
                  WES,
                  THU,
                  FRI,
                  SAT,
                  SUN
FROM TIMELIST
INNER JOIN CLASSES ON RTRIM(TIMELIST.WHO)=RTRIM(CLASSES.CLASSNO)
INNER JOIN COURSEPLAN ON TIMELIST.YEAR=COURSEPLAN.YEAR
  AND TIMELIST.TERM=COURSEPLAN.TERM
  AND RTRIM(COURSEPLAN.CLASSNO)=RTRIM(TIMELIST.WHO)
WHERE TIMELIST.TYPE='C'
  AND TIMELIST.YEAR=:YEAR
  AND TIMELIST.TERM=:TERM
  AND RTRIM(COURSEPLAN.COURSENO)+RTRIM(COURSEPLAN.[GROUP]) IN (:COURSENO)
ORDER BY WHO
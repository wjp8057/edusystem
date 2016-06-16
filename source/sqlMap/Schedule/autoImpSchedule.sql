DELETE SCHEDULE WHERE YEAR=:YEAR1 AND TERM=:TERM;


INSERT INTO TEACHERPLAN (YEAR,TERM,MAP,TEACHERNO,HOURS,UNIT,TASK,AMOUNT,TOSCHEDULE)
SELECT
scheduleplan.YEAR, scheduleplan.TERM,
scheduleplan.RECNO AS SCHEDULEPLANRECNO,
teacherplan.TEACHERNO,
teacherplan.HOURS,
teacherplan.UNIT,
teacherplan.TASK,
teacherplan.AMOUNT,
teacherplan.TOSCHEDULE
FROM SCHEDULEPLAN LEFT OUTER JOIN TEACHERPLAN ON SCHEDULEPLAN.RECNO=TEACHERPLAN.MAP
WHERE  (scheduleplan.YEAR = 2013)
   AND  (scheduleplan.TERM = 1)
ORDER BY scheduleplan.COURSENO,scheduleplan.[GROUP]










',N'@P1 varchar(4),@P2 smallint','2013',1






exec sp_executesql N'INSERT INTO TEACHERPLAN
(YEAR,TERM,MAP,TEACHERNO,HOURS,UNIT,TASK,AMOUNT,TOSCHEDULE)
VALUES
(@P1,@P2,@P3,@P4,@P5,@P6,@P7,@P8,@P9)
',N'@P1 varchar(4),@P2 int,@P3 int,@P4 varchar(6),@P5 money,@P6 varchar(1),@P7 varchar(1),@P8 money,@P9 bit','2013',1,253352,'000000',$2.0000,'2','L',$2.0000,1







exec sp_executesql N'SELECT scheduleplan.YEAR,
scheduleplan.TERM,
scheduleplan.COURSENO,
scheduleplan.[GROUP],
scheduleplan.ESTIMATE,
scheduleplan.ATTENDENTS,
scheduleplan.ROOMTYPE,
scheduleplan.LHOURS,
scheduleplan.EHOURS,
scheduleplan.WEEKS,
scheduleplan.RECNO AS SCHEDULEPLANRECNO,
teacherplan.TEACHERNO,
teacherplan.HOURS,
teacherplan.UNIT,
teacherplan.REM,
scheduleplan.EMPROOM,
scheduleplan.TIME,
scheduleplan.SCHEDULED,
scheduleplan.EBATCH,
scheduleplan.DAYS,
scheduleplan.AREA,
teacherplan.TASK,
teacherplan.AMOUNT,
teacherplan.TOSCHEDULE,
teacherplan.RECNO,
courses.TYPE
FROM SCHEDULEPLAN LEFT OUTER JOIN TEACHERPLAN ON SCHEDULEPLAN.RECNO=TEACHERPLAN.MAP
JOIN COURSES ON SCHEDULEPLAN.COURSENO=COURSES.COURSENO
WHERE  (scheduleplan.YEAR = @P1)
   AND  (scheduleplan.TERM = @P2)
ORDER BY scheduleplan.COURSENO,scheduleplan.[GROUP]










',N'@P1 varchar(4),@P2 smallint','2013',1






exec sp_executesql N'INSERT INTO SCHEDULE
(YEAR,TERM,COURSENO,[GROUP],OEW,WEEKS,LE,MAP,UNIT,TIMER,ROOMR,EMPROOM)
VALUES
(@P1,@P2,@P3,@P4,@P5,@P6,@P7,@P8,@P9,@P10,@P11,@P12)
',N'@P1 varchar(4),@P2 smallint,@P3 varchar(7),@P4 varchar(2),@P5 varchar(1),@P6 int,@P7 varchar(1),@P8 int,@P9 varchar(1),@P10 varchar(2),@P11 varchar(1),@P12 varchar(20)',
'2013',1,'007D02A','01','O',262143,'A',79188,'3','0 ','U',''




exec sp_executesql N'SELECT * FROM SCHEDULE WHERE YEAR=@P1 AND TERM=@P2
',N'@P1 varchar(4),@P2 smallint','2013',1


exec sp_executesql N'UPDATE SCHEDULE SET ROOMNO=@P1 WHERE RECNO=@P2
',N'@P1 varchar(9),@P2 int','000000000',169783



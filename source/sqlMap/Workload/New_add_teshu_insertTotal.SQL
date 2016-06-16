insert into totalwork(Year,Term,Courseno,Coursename,W_Hours,W_number,Standard,Estimate,Attendent,Classname,status,school
,teachername,teacherno,classno,credit,kclx,xklx,mzks,[group],code,teacherschool,jiaozhengxishu)
select :year,:term,courseno,coursename,HOURS,0,0,:Estimate,:Attendent,'',1,SCHOOL,:teachername,:teacherno,'',
0,'S','E',HOURS,'',:code,'',1 from COURSES where  COURSENO=:COURSENO
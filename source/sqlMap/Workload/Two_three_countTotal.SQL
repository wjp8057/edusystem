 select count(*) as ROWS from(select row_number() over(order by dbo.getOne(T.courseno)) as row,
 dbo.getOne(T.coursename) as km,
 dbo.getOne(T.totalwork_id) as totalwork_id,
  dbo.getOne(T.jiaozhengxishu) as jiaozhengxishu,
 dbo.getOne(RTRIM(T.courseno)+RTRIM(T.[group])) as kh,
 dbo.getOne(T.credit) as xf,
 dbo.getOne(T.Estimate) as yjrs,
 dbo.getOne(ISNULL(T.Attendent,0))as renshu,
dbo.GROUP_CONCAT_MERGE(RTRIM(w.teacherno),',') as teacherno,
 dbo.getOne(T.W_number) as zs,
 dbo.getOne(T.xklx) as xklx,
 dbo.getOne(T.kclx) as kclx,
   dbo.getOne(COURSEAPPROACHES.[value]) as xklx2,
 dbo.getOne(COURSETYPEOPTIONS.[value]) as kclx2,
 dbo.getOne(T.mzks) as mzks,
 dbo.getOne(T.Standard) as bzb,
 dbo.getOne(T.status) as qrzt,
 dbo.getOne(RTRIM(T.code)) AS code,
 dbo.getOne(w.work_CF) as cfgzl,
 dbo.getOne(w.work) as grgzl,
 dbo.getOne(w.CFXS) as cfxs,
dbo.GROUP_CONCAT_MERGE(RTRIM(w.teachername),',') as jsxm,
 dbo.getOne(T.classname) as skbj,
 dbo.getOne(SCHOOLS.NAME) as teacherSCHOOL
 from totalwork as T
  inner join COURSETYPEOPTIONS ON T.kclx=COURSETYPEOPTIONS.NAME
 inner join COURSEAPPROACHES ON T.xklx=COURSEAPPROACHES.NAME
 left join work as w on w.Totalwork_id=T.totalwork_id
 left join TEACHERS ON T.teacherno=TEACHERS.teacherno
 left join SCHOOLS on SCHOOLS.SCHOOL=TEACHERS.SCHOOL
 where T.year=:YEAR AND T.term=:TERM and T.classno like :CLASSNO and RTRIM(T.courseno)+RTRIM(T.[group]) like :COURSENO
and T.teacherno like :TEACHERNO and T.code
like :CODE and T.status like :status and T.SCHOOL
like :myschool  and T.teacherschool like :teacherschool group by T.Totalwork_id)as b




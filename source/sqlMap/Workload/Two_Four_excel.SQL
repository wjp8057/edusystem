select
rtrim(t.courseno)+t.[group] as courseno,
courses.coursename,
courses.credits,
courses.HOURS,
courses.Lhours,
courses.EXPERIMENTS,
courses.COMPUTING,
courses.KHOURS,
courses.SHOURS,
courses.ZHOURS,
s.NAME as kkxy,
H.NAME as jsxy,
t.teacherno,
t.coursename,
t.teachername,
t.classno,S.NAME,
t.credit,ca.[VALUE] as xklx,
ct.[VALUE] as kclx,mzks,
t.Estimate,t.Attendent,
case
		when code='M1' then
      case
						when CAST(t.Attendent as int)=0
							then
								0
					  ELSE
						cast((1+(CAST(t.Attendent as decimal(10,3))-CAST(t.Standard as decimal(10,3)))/CAST(t.Attendent as decimal(10,3))) as decimal(10,3))
			end
  else
     0
end
as bxxs,
t.W_number,
teachertype.[value] as gwlx,
positions.[value] as zc,
t.Standard,t.Classname,
t.jiaozhengxishu,w.work,w.work*w.CFXS as cfgzl,
w.CFXS
from totalwork t
inner join work w on t.Totalwork_id=w.Totalwork_id
inner join courses on t.courseno=courses.courseno
inner join COURSETYPEOPTIONS ct on ct.NAME=t.kclx
inner join COURSEAPPROACHES ca on ca.NAME=t.xklx
inner join teachers on t.teacherno=teachers.teacherno
left join SCHOOLS S ON S.SCHOOL=courses.SCHOOL
left join (select * from schools) as h on H.SCHOOL=t.teacherschool
left join TEACHERTYPE ON teachers.type=teachertype.name
left join positions on POSITIONS.name=teachers.position
where t.classno like :CLASSNO and RTRIM(t.courseno)+t.[group] like :COURSENO
and t.teacherno like :TEACHERNO and t.code
like :CODE  and courses.SCHOOL
like :SCHOOL
order by rtrim(t.courseno)+t.[group]


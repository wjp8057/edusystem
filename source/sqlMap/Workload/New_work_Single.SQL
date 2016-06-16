update work set work=(
select
case
  when code='M1' then
      W_number*CAST(mzks as decimal(10,2))*jiaozhengxishu/cishu.cs
  when code='M21' or code='M22'
         then CAST(TOTAL as decimal(10,2))*0.5/cishu.cs
  when code='M23'
       then CAST(Attendent as int)*W_number*0.15/cishu.cs
  when code='M5'
       then CAST(Attendent as int)*1.2/cishu.cs
  when code='M3'
         then
             case
                when substring(classno,3,1)=4
                  then CAST(Attendent as int)*4/cishu.cs
                else
                   CAST(Attendent as int)*2/cishu.cs
             end
  when code='M8'
          then
              CAST(Attendent as decimal(10,2))/CAST(Standard as decimal(10,2))*W_number*CAST(mzks as decimal(10,1))/cishu.cs
  when code='M9'
          then
              CAST(Attendent as decimal(10,2))/CAST(Standard as decimal(10,2))*W_number*CAST(mzks as decimal(10,1))*2/cishu.cs
end as work from totalwork as t
inner join
(select count(1) as cs,Courseno,[group] from totalwork group by courseno,[group]) as cishu on cishu.Courseno=t.Courseno and cishu.[group]=t.[group]
where work.Totalwork_id=t.Totalwork_id
) where Totalwork_id=:tid

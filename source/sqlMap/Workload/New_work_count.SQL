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
)




/*update work set work=(
select
case
  when code='M1' then
      W_number*CAST(mzks as decimal(10,2))*jiaozhengxishu
  when code='M21'
         then W_number*CAST(mzks as decimal(10,2))*0.5
  when code='M22' or code='M3'
         then CAST(Attendent as int)*W_number*0.15
  when code='M23'
         then W_number*CAST(mzks as decimal(10,2))*0.5
  when code='M4'
          then
             case
                when substring(classno,3,1)=4
                  then CAST(Attendent as int)*4
                else
                   CAST(Attendent as int)*2
             end
  when code='M10'
          then
              CAST(Attendent as decimal(10,2))/CAST(Standard as decimal(10,2))*W_number*CAST(mzks as decimal(10,1))
end as work from totalwork as t
where work.Totalwork_id=t.Totalwork_id)*/


 /*case when CAST(vw.renshu as int)=0 then tw.W_number*CAST(vw.mzks as decimal(10,1))*0.9



when vw.code='M1' and cast((1+(CAST(vw.renshu as decimal(10,2))-CAST(vw.bzb as decimal(10,2)))/CAST(vw.renshu as decimal(10,2)))as decimal(10,1))<0.9 then tw.W_number*CAST(vw.mzks as decimal(10,1))*0.9
																												when vw.code='M1' and cast((1+(CAST(vw.renshu as decimal(10,2))-CAST(vw.bzb as decimal(10,2)))/CAST(vw.renshu as decimal(10,2)))as decimal(10,1))>1.6 then tw.W_number*CAST(vw.mzks as decimal(10,1))*1.6
																												when vw.code='M1' then cast(tw.W_number*CAST(vw.mzks as decimal(10,2))*(1+(CAST(vw.renshu as decimal(10,2))-CAST(vw.bzb as decimal(10,2)))/CAST(vw.renshu as decimal(10,2)))as decimal(10,1))





	when vw.code='M21' then tw.W_number*vw.mzks*0.5
																												when vw.code='M22' then CAST(vw.renshu as int)*tw.W_number*0.15
																											  when vw.code='M23' then CAST(vw.renshu as int)*tw.W_number*0.5
																											  when vw.code='M3' then CAST(vw.renshu as int)*tw.W_number*0.15
																											  when vw.code='M4' then CAST(vw.renshu as int)*2
																											  when vw.code='M5' then tw.W_number*CAST(vw.mzks as decimal(10,1))*2
																											  when vw.code='M4' then CAST(vw.renshu as int)*1.2
																									ELSE 100
end as work*/
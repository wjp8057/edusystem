update work set work=(
  select
case
       when code='M1' then
			 case
				when w.work=0
					THen 0
			 else
		/*		CAST(W_number*CAST(mzks as decimal(10,2))*jiaozhengxishu*CAST(CAST(w.work as decimal(10,5))/CAST(:gzl as decimal(10,5)) as decimal(10,5))as decimal(10,1))*/
		     /* w.work*t.jiaozhengxishu/:jiaozhengxishu*1/:CFXS*w.CFXS*/
		      w.work*t.jiaozhengxishu/:jiaozhengxishu
			 end
  when code='M21'
         then CAST(TOTAL as decimal(10,2))*0.5/cishu.cs

  when code='M23'
       then CAST(Attendent as int)*W_number*0.15/cishu.cs
  when code='M3'
         then
             case
                when substring(classno,3,1)=4
                  then CAST(Attendent as int)*4/cishu.cs
                else
                   CAST(Attendent as int)*2/cishu.cs
             end
   when code='M5'
             then CAST(Attendent as int)*1.2/cishu.cs
  when code='M8'
          then
              CAST(Attendent as decimal(10,2))/CAST(Standard as decimal(10,2))*W_number*CAST(mzks as decimal(10,1))/cishu.cs
 when code='M9'
          then
              CAST(Attendent as decimal(10,2))/CAST(Standard as decimal(10,2))*W_number*CAST(mzks as decimal(10,1))*2/cishu.cs
end as work from totalwork as t inner join work w on w.Totalwork_id=t.Totalwork_id
inner join
(select count(1) as cs,Courseno,[group] from totalwork group by courseno,[group]) as cishu on cishu.Courseno=t.Courseno and cishu.[group]=t.[group]
where work.Totalwork_id=t.Totalwork_id
)


where work.Totalwork_id=:TID
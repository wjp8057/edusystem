update totalwork set jiaozhengxishu=(
select
case
  when code='M1' then
        case
						when CAST(Attendent as int)=0
							then
								case
                  when substring(rtrim(courseno),1,2)!='08'
											then
											   1
									else
                       0.9
								end
					  when substring(rtrim(courseno),1,2)!='08' and  cast((1+(CAST(Attendent as decimal(10,3))-CAST(Standard as decimal(10,3)))/CAST(Attendent as decimal(10,3)))as decimal(10,2))<1
                  then 1
						when cast((1+(CAST(Attendent as decimal(10,3))-CAST(Standard as decimal(10,3)))/CAST(Attendent as decimal(10,3)))as decimal(10,3))<0.9
							then 0.9
            when cast((1+(CAST(Attendent as decimal(10,3))-CAST(Standard as decimal(10,3)))/CAST(Attendent as decimal(10,3)))as decimal(10,3))>1.6
                then 1.6
            else
                  cast((1+(CAST(Attendent as decimal(10,3))-CAST(Standard as decimal(10,3)))/CAST(Attendent as decimal(10,3))) as decimal(10,3))
      end
  else
     1
  end
  as work from totalwork as t
where totalwork.Totalwork_id=t.Totalwork_id

) where totalwork_id=:tid



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
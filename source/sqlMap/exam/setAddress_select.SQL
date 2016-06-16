select * from (select row_number() over(order by ROOMNO) as row,ROOMNO as jsh,KW as kws,louNO as lh,menpaiNO as mph,ROOMNAME as jsmc,RECNO,
CASE WHEN status='1' then '启用' when status!='1' then '未启用' end as zt from TESTROOM
WHERE ROOMNO LIKE :ROOMNO AND ISNULL(ROOMNAME,'') LIKE :ROOMNAME AND ISNULL(menpaiNO,'') LIKE :MENPAI
)as b where b.row between :start and :end



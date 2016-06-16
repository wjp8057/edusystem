select count(*) as ROWS from (select row_number() over(order by ROOMNO) as row,ROOMNO as jsh,TESTERS as kws,BUILDING as lh,NO as mph,JSN as jsmc  from CLASSROOMS
WHERE ROOMNO LIKE :ROOMNO AND ISNULL(JSN,'') LIKE :ROOMNAME AND ISNULL(NO,'') LIKE :MENPAI
)as b
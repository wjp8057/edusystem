select count(b.row) from (SELECT row_number() over(order by Dbo_classrooms.NO)as row,Dbo_classrooms.ROOMNO,
    Dbo_classrooms.NO,
    Dbo_classrooms.JSN,
    Dbo_classrooms.BUILDING,
    Dbo_classrooms.AREA,
    Dbo_classrooms.SEATS,
    Dbo_classrooms.TESTERS,
    Dbo_classrooms.EQUIPMENT,
    Dbo_classrooms.STATUS,
    Dbo_classrooms.PRIORITY,
    Dbo_classrooms.USAGE,
    Dbo_classrooms.RESERVED,
    Dbo_areas.VALUE AS AREAVALUE,
    Dbo_roomoptions.VALUE AS EQUIPMENTVALUE,
    Dbo_roomusages.VALUE AS USAGEVALUE,
    Dbo_schools.NAME AS SCHOOLNAME,
    Dbo_classrooms.REM
FROM dbo.CLASSROOMS Dbo_classrooms,
    dbo.AREAS Dbo_areas,
    dbo.ROOMOPTIONS Dbo_roomoptions,
    dbo.ROOMUSAGES Dbo_roomusages,
    dbo.SCHOOLS Dbo_schools
WHERE  (Dbo_classrooms.AREA = Dbo_areas.NAME)
   AND (Dbo_classrooms.EQUIPMENT = Dbo_roomoptions.NAME)
   AND  (Dbo_classrooms.USAGE = Dbo_roomusages.NAME)
   AND  (Dbo_classrooms.PRIORITY = Dbo_schools.SCHOOL)
   AND  ( Dbo_classrooms.ROOMNO LIKE :ROOMNO)
   AND  (Dbo_classrooms.AREA LIKE :AREA)
   AND  (Dbo_classrooms.BUILDING LIKE :BUILDING)
   AND  (Dbo_classrooms.EQUIPMENT LIKE :EQUIPMENT)
   AND  (Dbo_classrooms.NO LIKE :NO)
   AND  (Dbo_classrooms.PRIORITY LIKE :PRIORITY)
   AND  ( (Dbo_classrooms.RESERVED = :RESERVED)  OR  (Dbo_classrooms.RESERVED = :UNRESERVED) )
   AND  ( (Dbo_classrooms.STATUS = :STATUS)  OR  (Dbo_classrooms.STATUS = :UNSTATUS) )
   AND  ( (Dbo_classrooms.SEATS >= :SEATSDOWN)  AND  (Dbo_classrooms.SEATS <= :SEATSUP) )
   AND  ( (Dbo_classrooms.TESTERS >= :TESTERSDOWN)  AND  (Dbo_classrooms.TESTERS <= :TESTERSUP) )
   AND  (Dbo_classrooms.USAGE LIKE :USAGE)  )as b

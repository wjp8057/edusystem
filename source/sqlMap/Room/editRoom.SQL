select  Dbo_classrooms.ROOMNO,
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
WHERE  Dbo_classrooms.AREA = Dbo_areas.NAME
   AND Dbo_classrooms.EQUIPMENT = Dbo_roomoptions.NAME
   AND  Dbo_classrooms.USAGE = Dbo_roomusages.NAME
   AND  Dbo_classrooms.PRIORITY = Dbo_schools.SCHOOL
   AND  Dbo_classrooms.ROOMNO =:ROOMNO


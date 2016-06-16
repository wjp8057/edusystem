SELECT personal.STUDENTNO,
personal.NAME,
SEXCODE.NAME AS SEX,
personal.BIRTHDAY,
personal.NATIONALITY,
personal.PARTY,
personal.EXAMNO,
personal.CLASS,
personal.FEATURE,
personal.PLANCLASS,
personal.POSTCODE,
personal.ADDRESS,
personal.TEL,
personal.MIDSCHOOL,
personal.MAJOR,
personal.YEARS,
personal.SCHOOL,
personal.SCORE,
personal.REM,
personal.ID,
personal.PROVINCE,
personal.BRANCH,
CONVERT(varchar(10),personal.DAYOFENROLL,20) as DATE,
nationalitycode.NAME NATIONALITY_NAME,
partycode.NAME PARTY_NAME,
classcode.NAME CLASS_NAME,
featurecode.NAME FEATURE_NAME,
planclasscode.NAME PLANCLASS_NAME,
majorcode.NAME MAJOR_NAME,
schools.NAME SCHOOL_NAME,
provincecode.NAME PROVINCE_NAME,
branchcode.NAME BRANCH_NAME
FROM personal
LEFT OUTER JOIN nationalitycode ON personal.NATIONALITY = nationalitycode.CODE
LEFT OUTER JOIN partycode ON personal.PARTY = partycode.CODE
LEFT OUTER JOIN classcode ON personal.CLASS = classcode.CODE
LEFT OUTER JOIN featurecode ON personal.FEATURE = featurecode.CODE
LEFT OUTER JOIN planclasscode ON personal.PLANCLASS = planclasscode.CODE
LEFT OUTER JOIN majorcode ON personal.MAJOR = majorcode.CODE
LEFT OUTER JOIN schools ON personal.SCHOOL = schools.SCHOOL
LEFT OUTER JOIN provincecode ON personal.PROVINCE = provincecode.CODE
LEFT OUTER JOIN branchcode ON personal.BRANCH = branchcode.CODE
LEFT OUTER JOIN SEXCODE ON PERSONAL.SEX=SEXCODE.CODE
WHERE  RTRIM(STUDENTNO) = :NEWSTUDENTNO OR RTRIM(EXAMNO) = :EXAMNO

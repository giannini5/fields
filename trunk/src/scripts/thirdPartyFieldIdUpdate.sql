use schedule;
select
    f.id,
    c.name,
    f.name,
    f.thirdPartyFieldId
from
    field as f
    join facility as c
        on f.facilityId = c.id
    join season as s on
        s.id = c.seasonId
        and s.name = '2022 - League'
where
    f.thirdPartyFieldId = 0;

set @from_season_name = '2021 - League';
set @to_season_name = '2022 - League';
select
    concat("update field set thirdPartyFieldId = ", ff.thirdPartyFieldId, " where id = ", tf.id, "; --", fc.name, tf.name)
from
    season as ts
    join field as tf on
        tf.thirdPartyFieldId = 0
    join facility as tc on
        tc.id = tf.facilityId
        and tc.seasonId = ts.id
    join facility as fc on
        fc.name = tc.name
    join field as ff on
        ff.facilityId = fc.id
        and ff.name = tf.name
        and ff.thirdPartyFieldId != 0
    join season as fs on
        fs.id = fc.seasonId
        and fs.name = @from_season_name
where
    ts.name = @to_season_name;


update field set thirdPartyFieldId = 1098 where id = 299; # Girsh ParkField 08
update field set thirdPartyFieldId = 1093 where id = 300; # Girsh ParkField 09
update field set thirdPartyFieldId = 1094 where id = 301; # Girsh ParkField 10
update field set thirdPartyFieldId = 1095 where id = 302; # Girsh ParkField 11
update field set thirdPartyFieldId = 280 where id = 303; # Girsh ParkField 12
update field set thirdPartyFieldId = 1085 where id = 304; # Girsh ParkField 13
update field set thirdPartyFieldId = 1086 where id = 305; # Girsh ParkField 14
update field set thirdPartyFieldId = 1081 where id = 306; # Girsh ParkField 15
update field set thirdPartyFieldId = 273 where id = 307; # Girsh ParkField 16
update field set thirdPartyFieldId = 274 where id = 308; # Girsh ParkField 17
update field set thirdPartyFieldId = 1089 where id = 309; # Girsh ParkField 18
update field set thirdPartyFieldId = 1091 where id = 310; # Girsh ParkField 19
update field set thirdPartyFieldId = 1092 where id = 311; # Girsh ParkField 20
update field set thirdPartyFieldId = 278 where id = 312; # Girsh ParkField 21
update field set thirdPartyFieldId = 1090 where id = 313; # Girsh ParkField 22
update field set thirdPartyFieldId = 1088 where id = 314; # Girsh ParkField 23
update field set thirdPartyFieldId = 1122 where id = 315; # UCSB Storke FieldField 1
update field set thirdPartyFieldId = 1123 where id = 316; # UCSB Storke FieldField 2
update field set thirdPartyFieldId = 263 where id = 317; # UCSB Storke FieldField 3
update field set thirdPartyFieldId = 264 where id = 318; # UCSB Storke FieldField 4'
update field set thirdPartyFieldId = 1121 where id = 319; # UCSB Storke FieldField 5
update field set thirdPartyFieldId = 841 where id = 320; # Rec Cen 1
update field set thirdPartyFieldId = 821 where id = 321; # Rec Cen 2
update field set thirdPartyFieldId = 1096 where id = 297; # Girsh ParkField 6
update field set thirdPartyFieldId = 1097 where id = 298; # Girsh ParkField 6

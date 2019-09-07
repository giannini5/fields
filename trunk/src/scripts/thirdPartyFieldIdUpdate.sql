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
        and s.name = '2018 - League';

set @from_season_name = '2018 - League';
set @to_season_name = '2019 - League';
select
    concat("update field set thirdPartyFieldId = ", ff.thirdPartyFieldId, " where id = ", tf.id, ";")
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

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

select
    concat("update field set thirdPartyFieldId = ", f2.thirdPartyFieldId, " where id = ", f.id, ";")
from
    field as f
    join facility as c on
        c.id = f.facilityId
    join facility as c2 on
        c2.name = c.name
    join field as f2 on
        f2.facilityId = c2.id
        and f2.name = f.name
        and f2.thirdPartyFieldId != 0
where
    f.thirdPartyFieldId = 0;


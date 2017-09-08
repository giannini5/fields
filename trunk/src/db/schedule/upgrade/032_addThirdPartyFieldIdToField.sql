drop procedure if exists 032_addWebYouthFieldIdToField;

delimiter $$
create procedure 032_addWebYouthFieldIdToField()
  begin

    if not exists(
        select
          *
        from
          information_schema.columns
        where
          TABLE_SCHEMA = DATABASE()
          and TABLE_NAME = 'field'
          and COLUMN_NAME = 'thirdPartyFieldId')
    then
      alter table field add COLUMN thirdPartyFieldId int default 0 after enabled;

      -- UCSB RecCen
      update field set thirdPartyFieldId = 841 where facilityId = 2 and name = "Field 1";
      update field set thirdPartyFieldId = 821 where facilityId = 2 and name = "Field 2";

      -- UCSB Storke
      update field set thirdPartyFieldId = 1122 where facilityId = 3 and name = "Field 1";
      update field set thirdPartyFieldId = 1123 where facilityId = 3 and name = "Field 2";
      update field set thirdPartyFieldId = 263 where facilityId = 3 and name = "Field 3";
      update field set thirdPartyFieldId = 264 where facilityId = 3 and name = "Field 4";
      update field set thirdPartyFieldId = 1121 where facilityId = 3 and name = "Field 5";

      -- Viola
      update field set thirdPartyFieldId = 1061 where facilityId = 4 and name = "Field 1";

      -- Girsh
      update field set thirdPartyFieldId = 1093 where facilityId = 1 and name = "Field 08";
      update field set thirdPartyFieldId = 1096 where facilityId = 1 and name = "Field 09";
      update field set thirdPartyFieldId = 1099 where facilityId = 1 and name = "Field 10";
      update field set thirdPartyFieldId = 1085 where facilityId = 1 and name = "Field 11";
      update field set thirdPartyFieldId = 1086 where facilityId = 1 and name = "Field 12";
      update field set thirdPartyFieldId = 1081 where facilityId = 1 and name = "Field 13";
      update field set thirdPartyFieldId = 280 where facilityId = 1 and name = "Field 14";
      update field set thirdPartyFieldId = 1091 where facilityId = 1 and name = "Field 15";
      update field set thirdPartyFieldId = 273 where facilityId = 1 and name = "Field 16";
      update field set thirdPartyFieldId = 274 where facilityId = 1 and name = "Field 17";
      update field set thirdPartyFieldId = 1097 where facilityId = 1 and name = "Field 18";
      update field set thirdPartyFieldId = 1094 where facilityId = 1 and name = "Field 19";
      update field set thirdPartyFieldId = 1098 where facilityId = 1 and name = "Field 20";
      update field set thirdPartyFieldId = 1095 where facilityId = 1 and name = "Field 21";
      update field set thirdPartyFieldId = 1089 where facilityId = 1 and name = "Field 22";
      update field set thirdPartyFieldId = 278 where facilityId = 1 and name = "Field 23";
      update field set thirdPartyFieldId = 1090 where facilityId = 1 and name = "Field 24";
      update field set thirdPartyFieldId = 1092 where facilityId = 1 and name = "Field 25";
      update field set thirdPartyFieldId = 1088 where facilityId = 1 and name = "Field 26";
    end if;
  end $$
delimiter ;

call 032_addWebYouthFieldIdToField();
drop procedure if exists 032_addWebYouthFieldIdToField;

-- �ڋq�����V�X�e�� �g���K��`�t�@�C��
 -- �t�@�C���쐬�� 2021/05/03
 -- �t�@�C���쐬�� UM �R��
 -- ----------------------

 -- �g���K�[�F �ڋq���i�V�K�j �X�V���F21/05/03
DROP TRIGGER IF EXISTS ins_customer;

CREATE TRIGGER ins_customer AFTER INSERT ON customer FOR EACH ROW
INSERT INTO  cu_customer ( core_id, customer_name, customer_name_kana, construction_number_require_flag , customer_system_use_flag, create_system_type, create_user_id, status )
VALUES ( 
      new.id                                        -- ��V�X�e���A�gID
    , new.customer_name                             -- �ڋq��Ж�
    , new.customer_name_kana                        -- �ڋq��Ж��F�J�i
    , new.construction_number_require_flag          -- �H���ԍ��K�{�t���O
    , new.customer_system_use_flag                  -- �ڋq�V�X�e�����p�L��
    , 1                                             -- �Œ�l�F��V�X�e��
    , 0                                             -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X
);

 -- �g���K�[�F �ڋq���i�X�V�j �X�V���F21/05/03
DROP TRIGGER IF EXISTS upd_customer;

CREATE TRIGGER upd_customer AFTER UPDATE ON customer FOR EACH ROW
BEGIN
 -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
IF EXISTS ( SELECT 1 FROM cu_customer WHERE core_id = new.id ) THEN
  UPDATE cu_customer
    SET 
     customer_name                    = new.customer_name                     -- �ڋq��Ж�
    ,customer_name_kana               = new.customer_name_kana                -- �ڋq��Ж��F�J�i
    ,construction_number_require_flag = new.construction_number_require_flag  -- �H���ԍ��K�{�t���O
    ,customer_system_use_flag         = new.customer_system_use_flag          -- �ڋq�V�X�e�����p�L��
    ,update_system_type               = 1                                     -- �Œ�l�F��V�X�e��
    ,update_user_id                   = 0                                     -- �Œ�l�F�V�X�e�������A�g
    ,status                           = ISNULL( new.delete_timestamp )        -- �X�e�[�^�X
  WHERE core_id = new.id
  ;
 -- �A�g�ς݂̃f�[�^�����݂��Ȃ��ꍇ�͓o�^���s��
ELSE
  INSERT INTO  cu_customer ( 
       core_id, 
       customer_name, 
       customer_name_kana, 
       construction_number_require_flag , 
       customer_system_use_flag, 
       create_system_type, 
       create_user_id, 
       status )
  VALUES ( 
        new.id                                        -- ��V�X�e���A�gID
      , new.customer_name                             -- �ڋq��Ж�
      , new.customer_name_kana                        -- �ڋq��Ж��F�J�i
      , new.construction_number_require_flag          -- �H���ԍ��K�{�t���O
      , new.customer_system_use_flag                  -- �ڋq�V�X�e�����p�L��
      , 1                                             -- �Œ�l�F��V�X�e��
      , 0                                             -- �Œ�l�F�V�X�e�������A�g
      , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X
  );
END IF;
END;


 -- �g���K�[�F �ڋq�x�X���i�o�^�j �X�V���F21/05/04
DROP TRIGGER IF EXISTS ins_customer_branch;


CREATE TRIGGER ins_customer_branch AFTER INSERT ON customer_branch FOR EACH ROW
BEGIN

DECLARE _customer_id int;

SELECT customer_id 
INTO _customer_id
FROM cu_customer
WHERE core_id = new.customer_id; 

INSERT INTO cu_customer_branch ( 
    customer_id
  , core_id
  , customer_branch_name
  , customer_branch_name_kana
  , zip
  , prefecture
  , city
  , address
  , create_system_type
  , create_user_id
  , status 
)
VALUES (
      _customer_id                                -- �ڋqID
    , new.id                                        -- ��V�X�e���A�gID
    , new.customer_branch_name                      -- �ڋq�x�X��
    , new.customer_branch_name_kana                 -- �ڋq�x�X���F�J�i
    , new.customer_branch_zip_code                  -- �ڋq�x�X���ݒn�F�X�֔ԍ�
    , new.customer_branch_prefecture                -- �ڋq�x�X���ݒn�F�s���{���R�[�h
    , new.customer_branch_city                      -- �ڋq�x�X���ݒn�F�s�撬��
    , new.customer_branch_address                   -- �ڋq�x�X���ݒn�F�Ԓn�i�����ȍ~�j
    , 1                                             -- �Œ�l�F��V�X�e��
    , 0                                             -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X
);

END;

 -- �g���K�[�F �ڋq�x�X���i�X�V�j �X�V���F21/06/09
DROP TRIGGER IF EXISTS upd_customer_branch;

CREATE TRIGGER upd_customer_branch AFTER UPDATE ON customer_branch FOR EACH ROW
BEGIN

DECLARE _customer_id int;

 -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
IF EXISTS ( SELECT 1 FROM cu_customer_branch WHERE core_id = new.id ) THEN
  UPDATE cu_customer_branch
    SET 
     customer_branch_name             = new.customer_branch_name              -- �ڋq�x�X��
    ,customer_branch_name_kana        = new.customer_branch_name_kana         -- �ڋq�x�X���F�J�i
    ,zip                              = new.customer_branch_zip_code          -- �ڋq�x�X���ݒn�F�X�֔ԍ�
    ,prefecture                       = new.customer_branch_prefecture        -- �ڋq�x�X���ݒn�F�s���{���R�[�h
    ,city                             = new.customer_branch_city              -- �ڋq�x�X���ݒn�F�s�撬��
    ,address                          = new.customer_branch_address           -- �ڋq�x�X���ݒn�F�Ԓn�i�����ȍ~�j
    ,update_system_type               = 1                                     -- �Œ�l�F��V�X�e��
    ,update_user_id                   = 0                                     -- �Œ�l�F�V�X�e�������A�g
    ,status                           = ISNULL( new.delete_timestamp )        -- �X�e�[�^�X
  WHERE core_id = new.id
  ;
 -- �A�g�ς݂̃f�[�^�����݂��Ȃ��ꍇ�͓o�^���s��
ELSE

  SELECT customer_id 
  INTO _customer_id
  FROM cu_customer
  WHERE core_id = new.customer_id; 

  INSERT INTO cu_customer_branch ( 
    customer_id
  , core_id
  , customer_branch_name
  , customer_branch_name_kana
  , zip
  , prefecture
  , city
  , address
  , create_system_type
  , create_user_id
  , status 
  )
  VALUES (
      _customer_id                                -- �ڋqID
    , new.id                                        -- ��V�X�e���A�gID
    , new.customer_branch_name                      -- �ڋq�x�X��
    , new.customer_branch_name_kana                 -- �ڋq�x�X���F�J�i
    , new.customer_branch_zip_code                  -- �ڋq�x�X���ݒn�F�X�֔ԍ�
    , new.customer_branch_prefecture                -- �ڋq�x�X���ݒn�F�s���{���R�[�h
    , new.customer_branch_city                      -- �ڋq�x�X���ݒn�F�s�撬��
    , new.customer_branch_address                   -- �ڋq�x�X���ݒn�F�Ԓn�i�����ȍ~�j
    , 1                                             -- �Œ�l�F��V�X�e��
    , 0                                             -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X
  );
END IF;
END;

 -- �g���K�[�F �ڋq�S���ҏ��i�o�^�j �X�V���F21/06/10
DROP TRIGGER IF EXISTS ins_customer_user;

CREATE TRIGGER ins_customer_user AFTER INSERT ON customer_user FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id int;

  -- ��V�X�e���œo�^�����ꍇ
  IF new.lastupdate_user_id <> 1 THEN  -- �ꎞ�I�ȑޔ������i�V�X�e����ʒǉ���ɏC���j

    SELECT customer_id,  customer_branch_id
    INTO  _customer_id, _customer_branch_id
    FROM  cu_customer_branch
    WHERE core_id = new.customer_branch_id;

    INSERT INTO cu_customer_user (
       customer_id
     , customer_branch_id
     , core_id
     , customer_user_name
     , customer_user_name_kana
     , customer_user_division_name
     , customer_user_email
     , customer_user_tel
     , customer_reminder_sms_flag
     , create_system_type
     , create_user_id
     , status
    )
    VALUES (
       _customer_id                                  -- �ڋq���ID
     , _customer_branch_id                           -- �ڋq�x�XID
     , new.id                                        -- ��V�X�e���A�gID
     , new.customer_user_name                        -- �ڋq�S���Җ�
     , new.customer_user_name_kana                   -- �ڋq�S���Җ��F�J�i
     , new.customer_user_division_name               -- �ڋq�S���ҕ�����
     , new.customer_user_email                       -- �ڋq�S���ҁF���[���A�h���X
     , new.customer_user_tel                         -- �ڋq�S���ҁF�g�ѓd�b�ԍ�
     , new.customer_reminder_sms_flag                -- �ڋq�S���ҁFSMS���}�C���h���t�L��
     , 1                                             -- �Œ�l�F��V�X�e��
     , 0                                             -- �Œ�l�F�V�X�e�������A�g
     , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X
    );

  END IF;
END;

 -- �g���K�[�F �ڋq�S���ҏ��i�X�V�j �X�V���F21/06/10
DROP TRIGGER IF EXISTS upd_customer_user;

CREATE TRIGGER upd_customer_user AFTER UPDATE ON customer_user FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id int;

  -- ��V�X�e���œo�^�����ꍇ
  IF new.lastupdate_user_id <> 1 THEN  -- �ꎞ�I�ȑޔ������i�V�X�e����ʒǉ���ɏC���j

    -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
    IF EXISTS ( SELECT 1 FROM cu_customer_user WHERE core_id = new.id ) THEN
      UPDATE cu_customer_user
      SET 
         customer_user_name             = new.customer_user_name               -- �ڋq�S���Җ�
        ,customer_user_name_kana        = new.customer_user_name_kana          -- �ڋq�S���Җ��F�J�i
        ,customer_user_division_name    = new.customer_user_division_name      -- �ڋq�S���ҕ�����
        ,customer_user_email            = new.customer_user_email              -- �ڋq�S���ҁF���[���A�h���X
        ,customer_user_tel              = new.customer_user_tel                -- �ڋq�S���ҁF�g�ѓd�b�ԍ�
        ,customer_reminder_sms_flag     = new.customer_reminder_sms_flag       -- �ڋq�S���ҁFSMS���}�C���h���t�L��
        ,update_system_type             = 1                                    -- �Œ�l�F��V�X�e��
        ,update_user_id                 = 0                                    -- �Œ�l�F�V�X�e�������A�g
        ,status                         = ISNULL( new.delete_timestamp )       -- �X�e�[�^�X
      WHERE core_id = new.id
      ;

  -- �A�g�ς݂̃f�[�^�����݂��Ȃ��ꍇ�͓o�^���s��
    ELSE

      SELECT customer_id,  customer_branch_id
      INTO  _customer_id, _customer_branch_id
      FROM  cu_customer_branch
      WHERE core_id = new.customer_branch_id;

      INSERT INTO cu_customer_user (
        customer_id
      , customer_branch_id
      , core_id
      , customer_user_name
      , customer_user_name_kana
      , customer_user_division_name
      , customer_user_email
      , customer_user_tel
      , customer_reminder_sms_flag
      , create_system_type
      , create_user_id
      , status
      )
      VALUES (
        _customer_id                                  -- �ڋq���ID
      , _customer_branch_id                           -- �ڋq�x�XID
      , new.id                                        -- ��V�X�e���A�gID
      , new.customer_user_name                        -- �ڋq�S���Җ�
      , new.customer_user_name_kana                   -- �ڋq�S���Җ��F�J�i
      , new.customer_user_division_name               -- �ڋq�S���ҕ�����
      , new.customer_user_email                       -- �ڋq�S���ҁF���[���A�h���X
      , new.customer_user_tel                         -- �ڋq�S���ҁF�g�ѓd�b�ԍ�
      , new.customer_reminder_sms_flag                -- �ڋq�S���ҁFSMS���}�C���h���t�L��
      , 1                                             -- �Œ�l�F��V�X�e��
      , 0                                             -- �Œ�l�F�V�X�e�������A�g
      , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X
      );
    END IF;
  END IF;
END;


 -- �g���K�[�F ���ԏ���i�o�^�j �X�V���F21/05/03
DROP TRIGGER IF EXISTS ins_parking;

CREATE TRIGGER ins_parking AFTER INSERT ON parking FOR EACH ROW
INSERT INTO cu_parking ( core_id, parking_name, parking_name_kana, latitude , longitude , create_system_type, create_user_id, status  )
VALUES (
      new.id                                    -- ��V�X�e���A�gID
    , new.parking_name                          -- ���ԏꖼ
    , new.parking_name_kana                     -- ���ԏꖼ�F�J�i
    , new.latitude                              -- �ܓx
    , new.longitude                             -- �o�x
    , 1                                         -- �Œ�l�F��V�X�e��
    , 0                                         -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )            -- �X�e�[�^�X
);


 -- �g���K�[�F ���ԏ���i�X�V�j �X�V���F21/05/03
DROP TRIGGER IF EXISTS upd_parking;

CREATE TRIGGER upd_parking AFTER UPDATE ON parking FOR EACH ROW
BEGIN
 -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
IF EXISTS ( SELECT 1 FROM cu_parking WHERE core_id = new.id ) THEN
  UPDATE cu_parking
    SET 
     parking_name             = new.parking_name                     -- ���ԏꖼ
    ,parking_name_kana        = new.parking_name_kana                -- ���ԏꖼ�F�J�i
    ,latitude                 = new.latitude                         -- �ܓx
    ,longitude                = new.longitude                        -- �o�x
    ,update_system_type       = 1                                    -- �Œ�l�F��V�X�e��
    ,update_user_id           = 0                                    -- �Œ�l�F�V�X�e�������A�g
    ,status                   = ISNULL( new.delete_timestamp )       -- �X�e�[�^�X
  WHERE core_id = new.id
  ;
 -- �A�g�ς݂̃f�[�^�����݂��Ȃ��ꍇ�͓o�^���s��
ELSE
  INSERT INTO cu_parking ( core_id, parking_name, parking_name_kana, latitude , longitude , create_system_type, create_user_id, status  )
  VALUES (
      new.id                                    -- ��V�X�e���A�gID
    , new.parking_name                          -- ���ԏꖼ
    , new.parking_name_kana                     -- ���ԏꖼ�F�J�i
    , new.latitude                              -- �ܓx
    , new.longitude                             -- �o�x
    , 1                                         -- �Œ�l�F��V�X�e��
    , 0                                         -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )            -- �X�e�[�^�X
  );
  END IF;
END;


 -- �g���K�[�F �����h�}�[�N�x�X�i�o�^�j �X�V���F21/05/03
DROP TRIGGER IF EXISTS ins_user_branch;

CREATE TRIGGER ins_user_branch AFTER INSERT ON user_branch FOR EACH ROW
INSERT 
INTO cu_branch( 
    core_id
    , branch_name
    , prefecture
    , city
    , address
    , tel
    , fax
    , zip_code
    , bank_account
    , create_system_type
    , create_user_id
    , status
)
VALUES (
      new.id                                    -- ��V�X�e���A�gID
    , new.user_branch_name                      -- �x�X��
    , new.user_branch_prefecture                -- �s���{���R�[�h
    , new.user_branch_city                      -- �s�撬��
    , new.user_branch_address                   -- �Ԓn�i�����ȍ~�j
    , new.user_branch_tel                       -- �d�b�ԍ�
    , new.user_branch_fax                       -- FAX
    , new.user_branch_zip_code                  -- �X�֔ԍ�
    , new.user_branch_deposit_bank_account      -- �~��������
    , 1                                         -- �Œ�l�F��V�X�e��
    , 0                                         -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )            -- �X�e�[�^�X
);

 -- �g���K�[�F �����h�}�[�N�x�X�i�X�V�j �X�V���F21/05/03
DROP TRIGGER IF EXISTS upd_user_branch;

CREATE TRIGGER upd_user_branch AFTER UPDATE ON user_branch FOR EACH ROW
BEGIN
 -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
IF EXISTS ( SELECT 1 FROM cu_branch WHERE core_id = new.id ) THEN
  UPDATE cu_branch
    SET 
     branch_name              = new.user_branch_name                   -- �x�X��
    ,prefecture               = new.user_branch_prefecture             -- �s���{���R�[�h
    ,city                     = new.user_branch_city                   -- �s�撬��
    ,address                  = new.user_branch_address                -- �Ԓn�i�����ȍ~�j
    ,tel                      = new.user_branch_tel                    -- �d�b�ԍ�
    ,fax                      = new.user_branch_fax                    -- FAX
    ,zip_code                 = new.user_branch_zip_code               -- �X�֔ԍ�
    ,bank_account             = new.user_branch_deposit_bank_account   -- �~��������    
    ,update_system_type       = 1                                      -- �Œ�l�F��V�X�e��
    ,update_user_id           = 0                                      -- �Œ�l�F�V�X�e�������A�g
    ,status                   = ISNULL( new.delete_timestamp )         -- �X�e�[�^�X
  WHERE core_id = new.id
  ;
 -- �A�g�ς݂̃f�[�^�����݂��Ȃ��ꍇ�͓o�^���s��
ELSE
  INSERT 
  INTO cu_branch( 
      core_id
    , branch_name
    , prefecture
    , city
    , address
    , tel
    , fax
    , zip_code
    , bank_account
    , create_system_type
    , create_user_id
    , status
  )
  VALUES (
      new.id                                    -- ��V�X�e���A�gID
    , new.user_branch_name                      -- �x�X��
    , new.user_branch_prefecture                -- �s���{���R�[�h
    , new.user_branch_city                      -- �s�撬��
    , new.user_branch_address                   -- �Ԓn�i�����ȍ~�j
    , new.user_branch_tel                       -- �d�b�ԍ�
    , new.user_branch_fax                       -- FAX
    , new.user_branch_zip_code                  -- �X�֔ԍ�
    , new.user_branch_deposit_bank_account      -- �~��������
    , 1                                         -- �Œ�l�F��V�X�e��
    , 0                                         -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )            -- �X�e�[�^�X 
  );
END IF;
END;

 -- �g���K�[�F �H�����i�o�^�j �X�V���F21/06/10
DROP TRIGGER IF EXISTS ins_project;

CREATE TRIGGER ins_project AFTER INSERT ON project FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id INT;

  -- �ڋq�����V�X�e������o�^�����ꍇ�͏������I������
  IF new.register_type = 0 THEN 

    -- �ڋq���̎擾
    SELECT 
      ccu.customer_id,ccu.customer_branch_id, ccu.customer_user_id
    INTO _customer_id, _customer_branch_id, _customer_user_id
    FROM cu_customer_user ccu
    WHERE ccu.core_id = new.customer_user_id;

    -- �����h�}�[�N�x�X���̎擾
    SELECT branch_id
    INTO _branch_id
    FROM cu_branch
    WHERE core_id = new.user_branch_id;

    INSERT INTO cu_project( 
        core_id                                   -- ��V�X�e���A�gID
      , customer_id                               -- �ڋq���ID
      , customer_branch_id                        -- �ڋq�x�XID
      , customer_user_id                          -- �ڋq�S����ID
      , branch_id                                 -- �x�XID
      , construction_number                       -- �H���ԍ�
      , site_name                                 -- ���ꖼ�^�@��
      , site_name_kana                            -- ���ꖼ�^�@���F�J�i
      , site_prefecture                           -- �s���{���R�[�h
      , site_city                                 -- �s�撬����
      , site_address                              -- �Ԓn�i�����ȍ~�j
      , latitude                                  -- �ܓx
      , longitude                                 -- �o�x
      , create_system_type                        -- �Œ�l�F��V�X�e��
      , create_user_id                            -- �Œ�l�F�V�X�e�������A�g
      , status                                    -- �X�e�[�^�X
    ) 
    VALUES (
      new.id                                    -- ��V�X�e���A�gID
    , _customer_id                              -- �ڋq���ID
    , _customer_branch_id                       -- �ڋq�x�XID
    , _customer_user_id                         -- �ڋq�S����ID
    , _branch_id                                -- �x�XID
    , new.construction_number                   -- �H���ԍ�
    , new.site_name                             -- ���ꖼ�^�@��
    , new.site_name_kana                        -- ���ꖼ�^�@���F�J�i
    , new.site_prefecture                       -- �s���{���R�[�h
    , new.site_city                             -- �s�撬����
    , new.site_address                          -- �Ԓn�i�����ȍ~�j
    , new.latitude                              -- �ܓx
    , new.longitude                             -- �o�x
    , 1                                         -- �Œ�l�F��V�X�e��
    , 0                                         -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )            -- �X�e�[�^�X 
    );

  END IF;
END;

 -- ���ۑ�
 -- �Z���R�[�h�A�s�撬���R�[�h�A�s�撬�����A�H���J�n���A�H���I���� �� ����֕ێ�
 -- ���ԏ꒲���˗����̓d�b�ԍ� �� �ڋq�����V�X�e���ɗv�ǉ�
 -- �����������t����  �� �ڋq�����V�X�e���ɗv�ǉ�
 -- �����ڋq���ID�A�����ڋq�x�XID�A�����ڋq�S����ID �� ������͎���Ȃ��O��



-- �g���K�[�F �H�����i�X�V�j �X�V���F21/06/09
DROP TRIGGER IF EXISTS upd_project;

CREATE TRIGGER upd_project AFTER UPDATE ON project FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id INT;

  -- ��V�X�e������̍X�V�̏ꍇ
  IF new.register_type = 0 THEN
  
    -- �ڋq���̎擾
    SELECT ccu.customer_id,ccu.customer_branch_id, ccu.customer_user_id
    INTO _customer_id, _customer_branch_id, _customer_user_id
    FROM cu_customer_user ccu
     INNER JOIN customer_user cu on ccu.core_id = cu.id
    WHERE cu.customer_user_natural_id = new.customer_user_natural_id;

    -- �����h�}�[�N�x�X���̎擾
    SELECT branch_id
    INTO _branch_id
    FROM cu_branch
    WHERE core_id = new.user_branch_id;

    -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
    IF EXISTS ( SELECT 1 FROM cu_project WHERE core_id = new.id ) THEN
  
      UPDATE cu_project
        SET 
         customer_id              = _customer_id                           -- �ڋq���ID
        ,customer_branch_id       = _customer_branch_id                    -- �ڋq�x�XID
        ,customer_user_id         = _customer_user_id                      -- �ڋq�S����ID
        ,branch_id                = _branch_id                             -- �x�XID
        ,construction_number      = new.construction_number                -- �H���ԍ�
        ,site_name                = new.site_name                          -- ���ꖼ�^�@��
        ,site_name_kana           = new.site_name_kana                     -- ���ꖼ�^�@���F�J�i
        ,site_prefecture          = new.site_prefecture                    -- �s���{���R�[�h 
        ,site_city                = new.site_city                          -- �s�撬���� 
        ,site_address             = new.site_address                       -- �Ԓn�i�����ȍ~�j 
        ,latitude                 = new.latitude                           -- �ܓx 
        ,longitude                = new.longitude                          -- �o�x 
        ,update_system_type       = 1                                      -- �Œ�l�F��V�X�e��
        ,update_user_id           = 0                                      -- �Œ�l�F�V�X�e�������A�g
        ,status                   = ISNULL( new.delete_timestamp )         -- �X�e�[�^�X
      WHERE core_id = new.id
      ;

    -- �A�g�ς݂̃f�[�^�����݂��Ȃ��ꍇ�͓o�^���s��
    ELSE
  
      INSERT INTO cu_project( 
        core_id
      , customer_id
      , customer_branch_id
      , customer_user_id
      , branch_id
      , construction_number
      , site_name
      , site_name_kana
      , site_prefecture
      , site_city
      , site_address
      , latitude
      , longitude
      , create_system_type
      , create_user_id
      , status
      ) 
      VALUES (
        new.id                                    -- ��V�X�e���A�gID
      , _customer_id                              -- �ڋq���ID
      , _customer_branch_id                       -- �ڋq�x�XID
      , _customer_user_id                         -- �ڋq�S����ID
      , _branch_id                                -- �x�XID
      , new.construction_number                   -- �H���ԍ�
      , new.site_name                             -- ���ꖼ�^�@��
      , new.site_name_kana                        -- ���ꖼ�^�@���F�J�i
      , new.site_prefecture                       -- �s���{���R�[�h
      , new.site_city                             -- �s�撬����
      , new.site_address                          -- �Ԓn�i�����ȍ~�j
      , new.latitude                              -- �ܓx
      , new.longitude                             -- �o�x
      , 1                                         -- �Œ�l�F��V�X�e��
      , 0                                         -- �Œ�l�F�V�X�e�������A�g
      , ISNULL( new.delete_timestamp )            -- �X�e�[�^�X 
      );
    END IF;
  END IF;
END;

 -- �g���K�[�F �˗��i�o�^�j �X�V���F21/06/09
DROP TRIGGER IF EXISTS ins_request;

CREATE TRIGGER ins_request AFTER INSERT ON request FOR EACH ROW
BEGIN

  DECLARE _project_id int;
  DECLARE 
    _subcontract_name
  , _subcontract_kana
  , _subcontract_branch_name
  , _subcontract_branch_kana
  , _subcontract_user_division_name
  , _subcontract_user_name
  , _subcontract_user_kana varchar(255);
  DECLARE _subcontract_user_email varchar(2048);
  DECLARE 
    _subcontract_user_tel
  , _subcontract_user_fax varchar(13);
  DECLARE _subcontract_reminder_sms_flag BOOL;

  -- ��V�X�e������̍X�V�̏ꍇ
  IF new.register_type = 0 THEN

    -- �H�����̎擾
    SELECT  project_id
    INTO   _project_id
    FROM   cu_project
    WHERE  core_id = new.project_id;
 
    -- �������̎擾
    SELECT  
      c.customer_name
     ,c.customer_name_kana
     ,cb.customer_branch_name
     ,cb.customer_branch_name_kana
     ,cu.customer_user_division_name
     ,cu.customer_user_name
     ,cu.customer_user_name_kana
     ,cu.customer_user_email
     ,cu.customer_user_tel
     ,cb.customer_branch_fax
     ,cu.customer_reminder_sms_flag
    INTO   
       _subcontract_name
     , _subcontract_kana
     , _subcontract_branch_name
     , _subcontract_branch_kana
     , _subcontract_user_division_name
     , _subcontract_user_name
     , _subcontract_user_kana
     , _subcontract_user_email
     , _subcontract_user_tel
     , _subcontract_user_fax
     , _subcontract_reminder_sms_flag
    FROM   project p
       INNER JOIN customer c ON p.subcontract_customer_id = c.id
       INNER JOIN customer_branch cb ON p.subcontract_customer_branch_id = cb.id
       INNER JOIN customer_user cu ON p.subcontract_customer_user_id = cu.id
    WHERE  p.id = new.project_id;

    -- ���ψ˗��i�o�^�j
    INSERT INTO cu_request( 
      project_id
    , core_id
    , request_date
    , estimate_deadline
    , request_type
    , want_start_date
    , want_end_date
    , car_qty
    , light_truck_qty
    , truck_qty
    , other_car_qty
    , other_car_detail
    , want_guide_type
    , cc_email
    , response_request_date
    , customer_other_request
    , request_other_deadline
    , request_other_start_date
    , request_other_end_date
    , request_other_qty
    , request_status
    , subcontract_want_guide_type
    , subcontract_name
    , subcontract_kana
    , subcontract_branch_name
    , subcontract_branch_kana
    , subcontract_user_division_name
    , subcontract_user_name
    , subcontract_user_kana
    , subcontract_user_email
    , subcontract_user_tel
    , subcontract_user_fax
    , subcontract_reminder_sms_flag
    , create_system_type
    , create_user_id
    , status
    ) 
    VALUES (
      _project_id                                   -- �H��ID
    , new.id                                        -- ��V�X�e���A�gID
    , new.request_date                              -- �˗���t��
    , new.estimate_deadline                         -- ���ϒ�o����
    , new.request_type                              -- �˗����
    , new.want_start_date                           -- ���p���ԁF�J�n
    , new.want_end_date                             -- ���p���ԁF�I��
    , new.car_qty                                   -- �䐔�F��p�ԁi�y�����ԁE�n�C�G�[�X���j
    , new.light_truck_qty                           -- �䐔�F�y�g���b�N
    , new.truck_qty                                 -- �䐔�F2���g���b�N
    , new.other_car_qty                             -- �䐔�F���̑��i�v�j
    , new.other_car_detail                          -- ���̑��ڍ�
    , new.want_guide_type                           -- �ē����@
    , new.cc_email                                  -- �ڋq���w�肷��CC���[���A�h���X
    , new.estimate_deadline                         -- �ڋq����̗v�]��
    , new.customer_other_request                    -- �ڋq����̗v�]�Ȃ�
    , new.request_other_deadline                    -- ���������
    , new.request_other_start_date                  -- �_��J�n��
    , new.request_other_end_date                    -- �_��I����
    , new.request_other_qty                         -- ��
    , new.survey_status                             -- ���ψ˗��X�e�[�^�X�^�����X�e�[�^�X
    , new.want_guide_type_subcontract               -- �ē����@�F�����p
    , _subcontract_name                             -- �����ڋq��Ж�
    , _subcontract_kana                             -- �����ڋq��Ж��F�J�i
    , _subcontract_branch_name                      -- �����ڋq�x�X��
    , _subcontract_branch_kana                      -- �����ڋq�x�X���F�J�i
    , _subcontract_user_division_name               -- �����ڋq������
    , _subcontract_user_name                        -- �����ڋq�S���Җ�
    , _subcontract_user_kana                        -- �����ڋq�S���Җ��F�J�i
    , _subcontract_user_email                       -- �����ڋq�S���҃��[���A�h���X
    , _subcontract_user_tel                         -- �����ڋq�S���Ҍg�єԍ�
    , _subcontract_user_fax                         -- �����ڋq�S����FAX�ԍ�
    , _subcontract_reminder_sms_flag                -- �����ڋq�S����SMS���}�C���h�L��
    , 1                                             -- �Œ�l�F��V�X�e��
    , 0                                             -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X 
    );
  END IF;
END;

-- �g���K�[�F ���ψ˗��i�X�V�j �X�V���F21/06/13
DROP TRIGGER IF EXISTS upd_request;

CREATE TRIGGER upd_request AFTER UPDATE ON request FOR EACH ROW
BEGIN

  DECLARE _project_id int;
  DECLARE 
    _subcontract_name
  , _subcontract_kana
  , _subcontract_branch_name
  , _subcontract_branch_kana
  , _subcontract_user_division_name
  , _subcontract_user_name
  , _subcontract_user_kana varchar(255);
  DECLARE _subcontract_user_email varchar(2048);
  DECLARE 
    _subcontract_user_tel
  , _subcontract_user_fax varchar(13);
  DECLARE _subcontract_reminder_sms_flag BOOL;

  -- ��V�X�e������̍X�V�̏ꍇ
  IF new.register_type = 0 THEN
  
    -- �H�����̎擾
    SELECT  project_id
    INTO   _project_id
    FROM   cu_project
    WHERE  core_id = new.project_id;
  
    -- �������̎擾
    SELECT  
      c.customer_name
     ,c.customer_name_kana
     ,cb.customer_branch_name
     ,cb.customer_branch_name_kana
     ,cu.customer_user_division_name
     ,cu.customer_user_name
     ,cu.customer_user_name_kana
     ,cu.customer_user_email
     ,cu.customer_user_tel
     ,cb.customer_branch_fax
     ,cu.customer_reminder_sms_flag
    INTO   
       _subcontract_name
     , _subcontract_kana
     , _subcontract_branch_name
     , _subcontract_branch_kana
     , _subcontract_user_division_name
     , _subcontract_user_name
     
     , _subcontract_user_kana
     , _subcontract_user_email
     , _subcontract_user_tel
     , _subcontract_user_fax
     , _subcontract_reminder_sms_flag
    FROM   project p
       INNER JOIN customer c ON p.subcontract_customer_id = c.id
       INNER JOIN customer_branch cb ON p.subcontract_customer_branch_id = cb.id
       INNER JOIN customer_user cu ON p.subcontract_customer_user_id = cu.id
    WHERE  p.id = new.project_id;

    -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
    IF EXISTS ( SELECT 1 FROM cu_request WHERE core_id = new.id ) THEN

      UPDATE cu_request
      SET 
        request_date                       = new.request_date                       -- �˗���t��
      , estimate_deadline                  = new.estimate_deadline                  -- ���ϒ�o����
      , request_type                       = new.request_type                       -- �˗����
      , want_start_date                    = new.want_start_date                    -- ���p���ԁF�J�n
      , want_end_date                      = new.want_end_date                      -- ���p���ԁF�I��
      , car_qty                            = new.car_qty                            -- �䐔�F��p�ԁi�y�����ԁE�n�C�G�[�X���j
      , light_truck_qty                    = new.light_truck_qty                    -- �䐔�F�y�g���b�N
      , truck_qty                          = new.truck_qty                          -- �䐔�F2���g���b�N
      , other_car_qty                      = new.other_car_qty                      -- �䐔�F���̑��i�v�j
      , other_car_detail                   = new.other_car_detail                   -- ���̑��ڍ�
      , want_guide_type                    = new.want_guide_type                    -- �ē����@
      , cc_email                           = new.cc_email                           -- �ڋq���w�肷��CC���[���A�h���X
      , response_request_date              = new.estimate_deadline                  -- �ڋq����̗v�]��
      , customer_other_request             = new.customer_other_request             -- �ڋq����̗v�]�Ȃ�
      , request_other_deadline             = new.request_other_deadline             -- ���������
      , request_other_start_date           = new.request_other_start_date           -- �_��J�n��
      , request_other_end_date             = new.request_other_end_date             -- �_��I����
      , request_other_qty                  = new.request_other_qty                  -- ��
      , request_status                     = new.survey_status                      -- ���ψ˗��X�e�[�^�X�^�����X�e�[�^�X
      , subcontract_want_guide_type        = new.want_guide_type_subcontract        -- �ē����@�F�����p
      , subcontract_name                   = _subcontract_name                      -- �����ڋq��Ж�
      , subcontract_kana                   = _subcontract_kana                      -- �����ڋq��Ж��F�J�i
      , subcontract_branch_name            = _subcontract_branch_name               -- �����ڋq�x�X��
      , subcontract_branch_kana            = _subcontract_branch_kana               -- �����ڋq�x�X���F�J�i
      , subcontract_user_division_name     = _subcontract_user_division_name        -- �����ڋq������
      , subcontract_user_name              = _subcontract_user_name                 -- �����ڋq�S���Җ�
      , subcontract_user_kana              = _subcontract_user_kana                 -- �����ڋq�S���Җ��F�J�i
      , subcontract_user_email             = _subcontract_user_email                -- �����ڋq�S���҃��[���A�h���X
      , subcontract_user_tel               = _subcontract_user_tel                  -- �����ڋq�S���Ҍg�єԍ�
      , subcontract_user_fax               = _subcontract_user_fax                  -- �����ڋq�S����FAX�ԍ�
      , subcontract_reminder_sms_flag      = _subcontract_reminder_sms_flag         -- �����ڋq�S����SMS���}�C���h�L��
      ,update_system_type                  = 1                                      -- �Œ�l�F��V�X�e��
      ,update_user_id                      = 0                                      -- �Œ�l�F�V�X�e�������A�g
      ,status                               = ISNULL( new.delete_timestamp )        -- �X�e�[�^�X
      WHERE core_id = new.id
      ;
 
    -- �A�g�ς݂̃f�[�^�����݂��Ȃ��ꍇ�͓o�^���s��
    ELSE

      -- ���ψ˗��i�o�^�j
      INSERT INTO cu_request( 
        project_id
      , core_id
      , request_date
      , estimate_deadline
      , request_type
      , want_start_date
      , want_end_date
      , car_qty
      , light_truck_qty
      , truck_qty
      , other_car_qty
      , other_car_detail
      , want_guide_type
      , cc_email
      , response_request_date
      , customer_other_request
      , request_other_deadline
      , request_other_start_date
      , request_other_end_date
      , request_other_qty
      , request_status
      , subcontract_want_guide_type
      , subcontract_name
      , subcontract_kana
      , subcontract_branch_name
      , subcontract_branch_kana
      , subcontract_user_division_name
      , subcontract_user_name
      , subcontract_user_kana
      , subcontract_user_email
      , subcontract_user_tel
      , subcontract_user_fax
      , subcontract_reminder_sms_flag
      , create_system_type
      , create_user_id
      , status
      ) 
      VALUES (
        _project_id                                   -- �H��ID
      , new.id                                        -- ��V�X�e���A�gID
      , new.request_date                              -- �˗���t��
      , new.estimate_deadline                         -- ���ϒ�o����
      , new.request_type                              -- �˗����
      , new.want_start_date                           -- ���p���ԁF�J�n
      , new.want_end_date                             -- ���p���ԁF�I��
      , new.car_qty                                   -- �䐔�F��p�ԁi�y�����ԁE�n�C�G�[�X���j
      , new.light_truck_qty                           -- �䐔�F�y�g���b�N
      , new.truck_qty                                 -- �䐔�F2���g���b�N
      , new.other_car_qty                             -- �䐔�F���̑��i�v�j
      , new.other_car_detail                          -- ���̑��ڍ�
      , new.want_guide_type                           -- �ē����@
      , new.cc_email                                  -- �ڋq���w�肷��CC���[���A�h���X
      , new.estimate_deadline                         -- �ڋq����̗v�]��
      , new.customer_other_request                    -- �ڋq����̗v�]�Ȃ�
      , new.request_other_deadline                    -- ���������
      , new.request_other_start_date                  -- �_��J�n��
      , new.request_other_end_date                    -- �_��I����
      , new.request_other_qty                         -- ��
      , new.survey_status                             -- ���ψ˗��X�e�[�^�X�^�����X�e�[�^�X
      , new.want_guide_type_subcontract               -- �ē����@�F�����p
      , _subcontract_name                             -- �����ڋq��Ж�
      , _subcontract_kana                             -- �����ڋq��Ж��F�J�i
      , _subcontract_branch_name                      -- �����ڋq�x�X��
      , _subcontract_branch_kana                      -- �����ڋq�x�X���F�J�i
      , _subcontract_user_division_name               -- �����ڋq������
      , _subcontract_user_name                        -- �����ڋq�S���Җ�
      , _subcontract_user_kana                        -- �����ڋq�S���Җ��F�J�i
      , _subcontract_user_email                       -- �����ڋq�S���҃��[���A�h���X
      , _subcontract_user_tel                         -- �����ڋq�S���Ҍg�єԍ�
      , _subcontract_user_fax                         -- �����ڋq�S����FAX�ԍ�
      , _subcontract_reminder_sms_flag                -- �����ڋq�S����SMS���}�C���h�L��
      , 1                                             -- �Œ�l�F��V�X�e��
      , 0                                             -- �Œ�l�F�V�X�e�������A�g
      , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X 
      );
    END IF;
  END IF;
END;

 -- �g���K�[�F ���ρi�o�^�j �X�V���F21/06/10
DROP TRIGGER IF EXISTS ins_estimate;

CREATE TRIGGER ins_estimate AFTER INSERT ON estimate FOR EACH ROW
BEGIN

 DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id int;
 DECLARE _request_status smallint;
 DECLARE _parking_name, _parking_name_kana varchar(255);
 
 -- ���ψ˗����̎擾
 SELECT
   request_id
 , project_id
 , request_status
 INTO
   _request_id
 , _project_id
 , _request_status
 FROM cu_request
 WHERE core_id = new.request_id;

 -- �������ϑ��t�ς݂̏ꍇ�̂݁A���σf�[�^���쐬����
 -- ���ԏ�ȊO�̌��ς̏ꍇ���������
 IF _request_status = 3 THEN
 
  -- ���ԏ���̎擾
   SELECT
        parking_id
      , parking_name
      , parking_name_kana
   INTO
        _parking_id
      , _parking_name
      , _parking_name_kana
   FROM cu_parking
   WHERE core_id = new.parking_id;

  -- �x�X���̎擾
   SELECT
      branch_id
   INTO
     _branch_id
   FROM cu_branch
   WHERE core_id = new.user_branch_id;
   
  -- ���σf�[�^�̍쐬
  INSERT INTO cu_estimate( 
      core_id
    , request_id
    , project_id
    , parking_id
    , branch_id
    , estimate_status
    , estimate_expire_date
    , estimate_cancel_check_flag
    , estimate_cancel_check_flag
    , survey_parking_name
    , survey_capacity_qty
    , survey_site_distance_minute
    , survey_site_distance_meter
    , survey_tax_in_flag
    , survey_total_amt
    , create_system_type
    , create_user_id
    , status
  ) 
  VALUES (
      new.id                                        -- ��V�X�e���A�gID
    , _request_id                                   -- ���ψ˗�ID
    , _project_id                                   -- �H��ID
    , _parking_id                                   -- ���ԏ�ID
    , _branch_id                                    -- �x�XID
    , case                                          -- ���σX�e�[�^�X
        when new.estimate_status = 3 then 1           -- 3:�������ϑ��t�ς� �� 1: �󒍑҂�
        when new.estimate_status = 4 then 4           -- 4:�� �� 4:��
        when new.estimate_status = 5 then 5           -- 5:�m�茩�ϑ��t�� �� 5:�m�茩�ϑ��t��         
        when new.estimate_status = 7 then 7           -- 7:�L�����Z�� �� 7:�L�����Z��
        when new.estimate_status = 8 then 8           -- 8:�����i�蓮�j �� 8:�L�����Z��
        when new.estimate_status = 9 then 9           -- 9:�����i�����j �� 9:�L�����Z��                                 
        else 0                                        -- 0:���쐬 
      end
    , new.estimate_expire_date                      -- ���ϗL��������
    , new.estimate_cancel_check_flag                -- ���σL�����Z���m�F�t���O
    , new.estimate_cancel_check_date                -- ���σL�����Z���m�F��
    , new.survey_parking_name                       -- �������ϒ��ԏ���F���ԏꖼ
    , new.survey_capacity_qty                       -- �������ό��Ϗ��F���ϑ䐔
    , new.survey_site_distance_minute               -- �������ό��Ϗ��F���ꋗ���i���j
    , new.survey_site_distance_meter                -- �������ό��Ϗ��F���ꋗ���i���[�g���j
    , new.survey_tax_in_flag                        -- �������ό��ϋ��z�F�ō��݃t���O
    , new.survey_total_amt                          -- �������ό��ϋ��z�F���ύ��v
    , 1                                             -- �Œ�l�F��V�X�e��
    , 0                                             -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X 
  );
   
  -- �_��f�[�^�̍쐬
  -- �_��f�[�^�̍쐬�O�ŃL�����Z���̏ꍇ�͌_��f�[�^�͍쐬���Ȃ�
  IF new.estimate_status = 5 THEN
  
    -- ����ID�̎擾
    SELECT estimate_id
    INTO   _estimate_id
    FROM   cu_estimate
    WHERE  core_id = new.id;
    
    INSERT INTO cu_contract( 
      core_id
    , project_id
    , estimate_id
    , parking_id
    , branch_id
    , contract_status
    , parking_name
    , parking_name_kana
    , quote_capacity_qty
    , quote_subtotal_amt
    , quote_tax_amt
    , quote_total_amt
    , purchase_order_upload_date
    , purchase_order_register_type
    , purchase_order_check_flag
    , order_schedule_date
    , order_process_date
    , quote_available_start_date
    , quote_available_end_date
    , extension_type
    , create_system_type
    , create_user_id
    , status
    )
    VALUES (
      new.id                                        -- ��V�X�e���A�gID
    , _project_id                                   -- �H��ID
    , _estimate_id                                  -- ����ID
    , _parking_id                                   -- ���ԏ�ID
    , _branch_id                                    -- �x�XID
    , 2                                             -- �_��X�e�[�^�X  2:�_�񏑏�����
    , _parking_name                                 -- ���ԏꖼ
    , _parking_name_kana                            -- ���ԏꖼ�F�J�i
    , new.quote_capacity_qty                        -- �m�茩�ϑ䐔
    , new.quote_subtotal_amt                        -- �m�茩�ρF���Ϗ��v
    , new.quote_tax_amt                             -- �m�茩�ρF�Ŋz
    , new.quote_total_amt                           -- �m�茩�ρF���v�z
    , new.purchase_order_upload_date                -- �������A�b�v���[�h��
    , new.purchase_order_register_type              -- �������A�b�v���[�h�V�X�e��
    , new.purchase_order_check_flag                 -- �������m�F�t���O
    , new.order_schedule_date                       -- �󒍗\���
    , new.order_process_date                        -- �󒍏�����
    , new.quote_available_start_date                -- �_��J�n��
    , new.quote_available_end_date                  -- �_��I����
    , 0                                           -- �_�񉄒��敪
    , 1                                           -- �Œ�l�F��V�X�e��
    , 0                                           -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )              -- �X�e�[�^�X 
    );

  END IF; 
 END IF;
END;

-- �g���K�[�F ���ψ˗��i�X�V�j �X�V���F21/06/10
DROP TRIGGER IF EXISTS upd_estimate;

CREATE TRIGGER upd_estimate AFTER UPDATE ON estimate FOR EACH ROW
BEGIN

 DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id int;
 DECLARE _request_status smallint;
 DECLARE _parking_name, _parking_name_kana varchar(255);

  IF new.lastupdate_user_id <> 1 THEN

 -- ���ψ˗����̎擾
 SELECT
   request_id
 , project_id
 , request_status
 INTO
   _request_id
 , _project_id
 , _request_status
 FROM cu_request
 WHERE core_id = new.request_id;
 
 -- ���ԏ���̎擾
 SELECT
      parking_id
    , parking_name
    , parking_name_kana
 INTO
      _parking_id
    , _parking_name
    , _parking_name_kana
 FROM cu_parking
 WHERE core_id = new.parking_id;

 -- �x�X���̎擾
 SELECT
    branch_id
 INTO
   _branch_id
 FROM cu_branch
 WHERE core_id = new.user_branch_id;
 
 -- ����ID�̎擾
 SELECT estimate_id
 INTO   _estimate_id
 FROM   cu_estimate
 WHERE  core_id = new.id;

   -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
 IF EXISTS ( SELECT 1 FROM cu_estimate WHERE core_id = new.id ) THEN

   -- ���σf�[�^���X�V
   UPDATE cu_estimate
   SET
      request_id                  = _request_id
    , project_id                  = _project_id
    , parking_id                  = _parking_id
    , branch_id                   = _branch_id
    , estimate_status             = 
       case                                          -- ���σX�e�[�^�X
        when new.estimate_status = 3 then 1           -- 3:�������ϑ��t�ς� �� 1: �󒍑҂�
        when new.estimate_status = 4 then 4           -- 4:�� �� 4:��
        when new.estimate_status = 5 then 5           -- 5:�m�茩�ϑ��t�� �� 5:�m�茩�ϑ��t��         
        when new.estimate_status = 7 then 7           -- 7:�L�����Z��  �� 7:�L�����Z��
        when new.estimate_status = 8 then 8           -- 8:�����i�蓮�j�� 8:�L�����Z��
        when new.estimate_status = 9 then 9           -- 9:�����i�����j�� 9:�L�����Z��                          
        else estimate_status                          -- �ύX�Ȃ� 
       end
    , estimate_expire_date        = new.estimate_expire_date  
    , estimate_cancel_check_flag  = new.estimate_cancel_check_flag
    , estimate_cancel_check_flag  = new.estimate_cancel_check_date
    , survey_parking_name         = new.survey_parking_name
    , survey_capacity_qty         = new.survey_capacity_qty 
    , survey_site_distance_minute = new.survey_site_distance_minute
    , survey_site_distance_meter  = new.survey_site_distance_meter 
    , survey_tax_in_flag          = new.survey_tax_in_flag
    , survey_total_amt            = new.survey_total_amt
    , create_system_type          = 1
    , create_user_id              = 0
    , status                      = ISNULL( new.delete_timestamp )
   WHERE core_id = new;

   -- �_��f�[�^�����݂���ꍇ  
   IF EXISTS ( SELECT 1 FROM cu_contract WHERE core_id = new.id ) THEN

     -- �_��f�[�^���X�V
     UPDATE cu_contract
     SET
       project_id                      = _project_id
     , estimate_id                     = _estimate_id
     , parking_id                      = _parking_id
     , branch_id                       = _branch_id
     , contract_status                 =
        case
          when new.estimate_status = 7 then 7
          else contract_status
        end
     , parking_name                    = _parking_name
     , parking_name_kana               = _parking_name_kana
     , quote_capacity_qty              = new.quote_capacity_qty
     , quote_subtotal_amt              = new.quote_subtotal_amt
     , quote_tax_amt                   = new.quote_tax_amt
     , quote_total_amt                 = new.quote_total_amt  
     , purchase_order_upload_date      = new.purchase_order_upload_date
     , purchase_order_register_type    = new.purchase_order_register_type
     , purchase_order_check_flag       = new.purchase_order_check_flag
     , order_schedule_date             = new.order_schedule_date
     , order_process_date              = new.order_process_date 
     , quote_available_start_date      = new.quote_available_start_date  
     , quote_available_end_date        = new.quote_available_end_date  
     , extension_type                  = extension_type
     , create_system_type              = 1
     , create_user_id                  = 0
     , status                          = ISNULL( new.delete_timestamp )
     WHERE core_id = new.id;

   -- �_��f�[�^�����݂��Ȃ��ꍇ 
   ELSE

     -- �_��f�[�^�̍쐬
     -- �_��f�[�^�̍쐬�O�ŃL�����Z���̏ꍇ�͌_��f�[�^�͍쐬���Ȃ�
     IF new.estimate_status = 5 THEN

      INSERT INTO cu_contract( 
        core_id
      , project_id
      , estimate_id
      , parking_id
      , branch_id
      , contract_status
      , parking_name
      , parking_name_kana
      , quote_capacity_qty
      , quote_subtotal_amt
      , quote_tax_amt
      , quote_total_amt
      , purchase_order_upload_date
      , purchase_order_register_type
      , purchase_order_check_flag
      , order_schedule_date
      , order_process_date
      , quote_available_start_date
      , quote_available_end_date
      , extension_type
      , create_system_type
      , create_user_id
      , status
      )
      VALUES (
        new.id                                        -- ��V�X�e���A�gID
      , _project_id                                   -- �H��ID
      , _estimate_id                                  -- ����ID
      , _parking_id                                   -- ���ԏ�ID
      , _branch_id                                    -- �x�XID
      , 2                                             -- �_��X�e�[�^�X  2:�_�񏑏�����
      , _parking_name                                 -- ���ԏꖼ
      , _parking_name_kana                            -- ���ԏꖼ�F�J�i
      , new.quote_capacity_qty                        -- �m�茩�ϑ䐔
      , new.quote_subtotal_amt                        -- �m�茩�ρF���Ϗ��v
      , new.quote_tax_amt                             -- �m�茩�ρF�Ŋz
      , new.quote_total_amt                           -- �m�茩�ρF���v�z
      , new.purchase_order_upload_date                -- �������A�b�v���[�h��
      , new.purchase_order_register_type              -- �������A�b�v���[�h�V�X�e��
      , new.purchase_order_check_flag                 -- �������m�F�t���O
      , new.order_schedule_date                       -- �󒍗\���
      , new.order_process_date                        -- �󒍏�����
      , new.quote_available_start_date                -- �_��J�n��
      , new.quote_available_end_date                  -- �_��I����
      , 0                                           -- �_�񉄒��敪
      , 1                                           -- �Œ�l�F��V�X�e��
      , 0                                           -- �Œ�l�F�V�X�e�������A�g
      , ISNULL( new.delete_timestamp )              -- �X�e�[�^�X 
      );

     END IF; 
   END IF;
 ELSE
 
   -- �������ϑ��t�ς݂̏ꍇ�̂݁A���σf�[�^���쐬����
   -- ���ԏ�ȊO�̌��ς̏ꍇ���������
   IF _request_status = 3 THEN

   -- ���σf�[�^�̍쐬
   INSERT INTO cu_estimate( 
      core_id
    , request_id
    , project_id
    , parking_id
    , branch_id
    , estimate_status
    , estimate_expire_date
    , estimate_cancel_check_flag
    , estimate_cancel_check_flag
    , survey_parking_name
    , survey_capacity_qty
    , survey_site_distance_minute
    , survey_site_distance_meter
    , survey_tax_in_flag
    , survey_total_amt
    , create_system_type
    , create_user_id
    , status
   ) 
   VALUES (
      new.id                                        -- ��V�X�e���A�gID
    , _request_id                                   -- ���ψ˗�ID
    , _project_id                                   -- �H��ID
    , _parking_id                                   -- ���ԏ�ID
    , _branch_id                                    -- �x�XID
    , case                                          -- ���σX�e�[�^�X
        when new.estimate_status = 3 then 1           -- 3:�������ϑ��t�ς� �� 1: �󒍑҂�
        when new.estimate_status = 4 then 4           -- 4:�� �� 4:��
        when new.estimate_status = 5 then 5           -- 5:�m�茩�ϑ��t�� �� 5:�m�茩�ϑ��t��         
        when new.estimate_status = 7 then 7           -- 7:�L�����Z�� �� 7:�L�����Z��                          
        else 0                                        -- 0:���쐬 
      end
    , new.estimate_expire_date                      -- ���ϗL��������
    , new.estimate_cancel_check_flag                -- ���σL�����Z���m�F�t���O
    , new.estimate_cancel_check_date                -- ���σL�����Z���m�F��
    , new.survey_parking_name                       -- �������ϒ��ԏ���F���ԏꖼ
    , new.survey_capacity_qty                       -- �������ό��Ϗ��F���ϑ䐔
    , new.survey_site_distance_minute               -- �������ό��Ϗ��F���ꋗ���i���j
    , new.survey_site_distance_meter                -- �������ό��Ϗ��F���ꋗ���i���[�g���j
    , new.survey_tax_in_flag                        -- �������ό��ϋ��z�F�ō��݃t���O
    , new.survey_total_amt                          -- �������ό��ϋ��z�F���ύ��v
    , 1                                             -- �Œ�l�F��V�X�e��
    , 0                                             -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X 
   );
   
   -- �_��f�[�^�̍쐬
   -- �_��f�[�^�̍쐬�O�ŃL�����Z���̏ꍇ�͌_��f�[�^�͍쐬���Ȃ�
   IF new.estimate_status = 5 THEN

    INSERT INTO cu_contract( 
      core_id
    , project_id
    , estimate_id
    , parking_id
    , branch_id
    , contract_status
    , parking_name
    , parking_name_kana
    , quote_capacity_qty
    , quote_subtotal_amt
    , quote_tax_amt
    , quote_total_amt
    , purchase_order_upload_date
    , purchase_order_register_type
    , purchase_order_check_flag
    , order_schedule_date
    , order_process_date
    , quote_available_start_date
    , quote_available_end_date
    , extension_type
    , create_system_type
    , create_user_id
    , status
    )
    VALUES (
      new.id                                        -- ��V�X�e���A�gID
    , _project_id                                   -- �H��ID
    , _estimate_id                                  -- ����ID
    , _parking_id                                   -- ���ԏ�ID
    , _branch_id                                    -- �x�XID
    , 2                                             -- �_��X�e�[�^�X  2:�_�񏑏�����
    , _parking_name                                 -- ���ԏꖼ
    , _parking_name_kana                            -- ���ԏꖼ�F�J�i
    , new.quote_capacity_qty                        -- �m�茩�ϑ䐔
    , new.quote_subtotal_amt                        -- �m�茩�ρF���Ϗ��v
    , new.quote_tax_amt                             -- �m�茩�ρF�Ŋz
    , new.quote_total_amt                           -- �m�茩�ρF���v�z
    , new.purchase_order_upload_date                -- �������A�b�v���[�h��
    , new.purchase_order_register_type              -- �������A�b�v���[�h�V�X�e��
    , new.purchase_order_check_flag                 -- �������m�F�t���O
    , new.order_schedule_date                       -- �󒍗\���
    , new.order_process_date                        -- �󒍏�����
    , new.quote_available_start_date                -- �_��J�n��
    , new.quote_available_end_date                  -- �_��I����
    , 0                                           -- �_�񉄒��敪
    , 1                                           -- �Œ�l�F��V�X�e��
    , 0                                           -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )              -- �X�e�[�^�X 
    );

       END IF;
     END IF; 
   END IF;
 END IF;
END;

 -- �g���K�[�F ���|���i�o�^�j �X�V���F21/05/04
DROP TRIGGER IF EXISTS ins_accounts_receivable;

-- ���ۑ� ��V�X�e�� �������Ƃ̕R�Â�
CREATE TRIGGER ins_accounts_receivable AFTER INSERT ON accounts_receivable FOR EACH ROW
BEGIN

DECLARE _project_id, _contract_id, _customer_id, _customer_branch_id, _customer_user_id, _parking_id int;

-- ���Ϗ�񂨂�эH�����̎擾


SELECT 
   c.project_id
  ,c.contract_id
  ,p.customer_id
  ,p.customer_branch_id
  ,p.customer_user_id
  ,c.parking_id
INTO
   _project_id
  ,_contract_id
  ,_customer_id
  ,_customer_branch_id
  ,_customer_user_id
  ,_parking_id
FROM cu_contract c
  INNER JOIN cu_project p ON p.project_id = c.project_id
WHERE core_id = new.estimate_id;

INSERT INTO cu_invoice( 
      core_id
    , project_id
    , contract_id
    , customer_id
    , customer_branch_id
    , customer_user_id
    , parking_id
    , invoice_amt
    , invoice_closing_date
    , payment_deadline
    , receivable_collect_total_amt
    , receivable_collect_finish_date
    , payment_status
    , reminder
    , create_system_type
    , create_user_id
    , status
) 
VALUES (
      new.id                                        -- ��V�X�e���A�gID
    , _project_id                                   -- �H��ID
    , _contract_id                                  -- �_��ID
    , _customer_id                                  -- �ڋq���ID
    , _customer_branch_id                           -- �ڋq�x�XID
    , _customer_user_id                             -- �ڋq�S����ID
    , _parking_id                                   -- ���ԏ�ID
    , new.invoice_amt                               -- �������z
    , new.invoice_closing_date                      -- ���������s��
    , new.payment_deadline                          -- �x��������
    , new.receivable_collect_total_amt              -- �����ϋ��z
    , new.receivable_collect_finish_date            -- �x��������
    , new.accounts_receivable_status                -- �x���X�e�[�^�X
    , new.payment_urge_flag                         -- ���t���O
    , 1                                             -- �Œ�l�F��V�X�e��
    , 0                                             -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X 
    );

END;

 -- �g���K�[�F ���|���i�X�V�j �X�V���F21/05/04
DROP TRIGGER IF EXISTS upd_accounts_receivable;

CREATE TRIGGER upd_accounts_receivable AFTER UPDATE ON accounts_receivable FOR EACH ROW
BEGIN

 DECLARE _project_id, _contract_id, _customer_id, _customer_branch_id, _customer_user_id, _parking_id int;

 -- ���Ϗ�񂨂�эH�����̎擾
 SELECT 
   c.project_id
  ,c.contract_id
  ,p.customer_id
  ,p.customer_branch_id
  ,p.customer_user_id
  ,c.parking_id
 INTO
   _project_id
  ,_contract_id
  ,_customer_id
  ,_customer_branch_id
  ,_customer_user_id
  ,_parking_id
 FROM cu_contract c
  INNER JOIN cu_project p ON p.project_id = c.project_id
 WHERE core_id = new.estimate_id;

 -- ���łɘA�g�ς݂̃f�[�^�����݂���ꍇ�͍X�V���s��
 IF EXISTS ( SELECT 1 FROM cu_estimate WHERE core_id = new.id ) THEN

  UPDATE cu_invoice
  SET
      project_id                       = _project_id
    , contract_id                      = _contract_id
    , customer_id                      = _customer_id
    , customer_branch_id               = _customer_branch_id
    , customer_user_id                 = _customer_user_id
    , parking_id                       = _parking_id
    , invoice_amt                      = new.invoice_amt
    , invoice_closing_date             = new.invoice_closing_date
    , payment_deadline                 = new.payment_deadline
    , receivable_collect_total_amt     = new.receivable_collect_total_amt
    , receivable_collect_finish_date   = new.receivable_collect_finish_date
    , payment_status                   = new.accounts_receivable_status
    , reminder                         = new.payment_urge_flag
    , create_system_type               = 1
    , create_user_id                   = 0
    , status                           = ISNULL( new.delete_timestamp )
  WHERE core_id = new.id;
  
 ELSE
  INSERT INTO cu_invoice( 
      core_id
    , project_id
    , contract_id
    , customer_id
    , customer_branch_id
    , customer_user_id
    , parking_id
    , invoice_amt
    , invoice_closing_date
    , payment_deadline
    , receivable_collect_total_amt
    , receivable_collect_finish_date
    , payment_status
    , reminder
    , create_system_type
    , create_user_id
    , status
 ) 
 VALUES (
      new.id                                        -- ��V�X�e���A�gID
    , _project_id                                   -- �H��ID
    , _contract_id                                  -- �_��ID
    , _customer_id                                  -- �ڋq���ID
    , _customer_branch_id                           -- �ڋq�x�XID
    , _customer_user_id                             -- �ڋq�S����ID
    , _parking_id                                   -- ���ԏ�ID
    , new.invoice_amt                               -- �������z
    , new.invoice_closing_date                      -- ���������s��
    , new.payment_deadline                          -- �x��������
    , new.receivable_collect_total_amt              -- �����ϋ��z
    , new.receivable_collect_finish_date            -- �x��������
    , new.accounts_receivable_status                -- �x���X�e�[�^�X
    , new.payment_urge_flag                         -- ���t���O
    , 1                                             -- �Œ�l�F��V�X�e��
    , 0                                             -- �Œ�l�F�V�X�e�������A�g
    , ISNULL( new.delete_timestamp )                -- �X�e�[�^�X 
    );
 END IF;
END;


-- �g���K�[�F �t�@�C���i�o�^�j �X�V���F21/04/30
DROP TRIGGER IF EXISTS ins_cu_file;

CREATE TRIGGER ins_cu_file BEFORE INSERT ON cu_file FOR EACH ROW
BEGIN

DECLARE _project_id, _request_id, _estimate_id, _contract_id, _invoice_id int;

 -- �H��, ���b�Z�[�W
 IF ( new.file_type = 1 OR new.file_type = 7 ) THEN

   SELECT project_id
   INTO _project_id
   FROM cu_project
   WHERE 
     case new.create_system_type
       when 1 then core_id            -- ��V�X�e��
       when 2 then project_id         -- �ڋq�����V�X�e��
       else project_id
     end =  new.ref_id;

 -- ���ψ˗�
 ELSEIF new.file_type = 2  THEN
  
  -- ���ψ˗��̎擾
  SELECT project_id, request_id
  INTO _project_id, _request_id
  FROM cu_request
  WHERE 
     case new.create_system_type
       when 1 then core_id            -- ��V�X�e��
       when 2 then request_id         -- �ڋq�����V�X�e��
       else request_id
     end =  new.ref_id;
  
 -- ����  �^ �ڋq�����V�X�e��
 ELSEIF  new.file_type = 3  THEN
  
  -- ���ς̎擾
  SELECT project_id, request_id, estimate_id
  INTO _project_id ,_request_id, _estimate_id
  FROM cu_estimate
  WHERE 
     case new.create_system_type
       when 1 then core_id            -- ��V�X�e��
       when 2 then estimate_id        -- �ڋq�����V�X�e��
       else estimate_id
     end =  new.ref_id;

 -- �����A�_��  �^ �ڋq�����V�X�e��
 ELSEIF ( new.file_type = 4 OR new.file_type = 5  )  THEN

  -- �_��̎擾
  SELECT project_id, estimate_id, contract_id
  INTO _project_id ,_estimate_id, _contract_id
  FROM cu_contract
  WHERE 
     case new.create_system_type
       when 1 then core_id            -- ��V�X�e��
       when 2 then contract_id        -- �ڋq�����V�X�e��
       else contract_id
     end =  new.ref_id;

  -- ���ς̎擾
  SELECT request_id
  INTO _request_id
  FROM cu_estimate
  WHERE estimate_id = _estimate_id;

 -- ����  �^ �ڋq�����V�X�e��
 ELSEIF  new.file_type = 6   THEN

  -- �����̎擾
  -- ���ۑ� �����̂݊�Ƃ̘A�g���@������
  SELECT project_id, contract_id, invoice_id
  INTO _project_id , _contract_id, _invoice_id
  FROM cu_invoice
  WHERE 
     case new.create_system_type
       when 1 then core_id            -- ��V�X�e��
       when 2 then invoice_id        -- �ڋq�����V�X�e��
       else invoice_id
     end =  new.ref_id;

  -- �_��̎擾
  SELECT estimate_id
  INTO  _estimate_id
  FROM cu_contract
  WHERE contract_id = _contract_id;
 
   -- ���ς̎擾
  SELECT request_id
  INTO _request_id
  FROM cu_estimate
  WHERE estimate_id = _estimate_id;
 
 END IF;

 SET  new.project_id  = _project_id
    , new.request_id  = _request_id
    , new.estimate_id = _estimate_id
    , new.contract_id = _contract_id
    , new.invoice_id  = _invoice_id;

END;

-- �g���K�[�F ���b�Z�[�W�i�X�V�j �X�V���F21/04/30
DROP TRIGGER IF EXISTS upd_cu_message;

CREATE TRIGGER upd_cu_message AFTER UPDATE ON cu_message FOR EACH ROW
INSERT INTO cu_message_history
  ( message_id, project_id, body, file_id, edit, already_read, create_date, create_user_id, create_system_type, status )
VALUES (  
  old.message_id, 
  old.project_id, 
  old.body,
  old.file_id, 
  old.edit, 
  old.already_read, 
  old.update_date, 
  old.update_user_id, 
  old.update_system_type, 
  old.status
 );


-- �g���K�[�F �H�����(�o�^) �X�V���F21/06/09
DROP TRIGGER IF EXISTS ins_cu_project;

CREATE TRIGGER ins_cu_project  BEFORE INSERT ON cu_project FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id INT;
  DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id  VARCHAR(255);

  -- �ڋq�V�X�e���ɂ��X�V�̏ꍇ
  IF new.create_system_type = 2 THEN

    -- �ڋq�A�ڋq�x�X�A�ڋq�S���҂̎擾
    SELECT 
      c.customer_id, 
      c.customer_natural_id,
      c.customer_branch_id,
      c.customer_branch_natural_id,
      c.id,
      c.customer_user_natural_id
    INTO 
      _customer_id, 
      _customer_natural_id,
      _customer_branch_id,
      _customer_branch_natural_id,
      _customer_user_id,
      _customer_user_natural_id
    FROM customer_user c
      INNER JOIN cu_customer_user cc ON cc.core_id = c.id
    WHERE cc.customer_user_id = new.customer_user_id;
  
    -- �x�X�����擾
    SELECT 
      id,
      user_branch_natural_id
    INTO 
      _branch_id,
      _branch_natural_id
    FROM user_branch ub
      INNER JOIN cu_branch cb ON cb.core_id = ub.id
      INNER JOIN cu_branch_area a ON a.branch_id = cb.branch_id
    WHERE a.prefecture = new.site_prefecture;

    -- �H��ID�̐���
    SELECT CONCAT(substr(current_date,3,2), 'C',  LPAD( count(*)+1, 7, 0))
    INTO _project_natural_id 
    FROM project 
    WHERE substr(current_date,3,2) = substr(project_natural_id,1,2);
  
    -- �H�����̓o�^
    INSERT INTO project (
      project_natural_id,
      customer_id,
      customer_natural_id,
      customer_branch_id,
      customer_branch_natural_id,
      customer_user_id,
      customer_user_natural_id,
      lastupdate_user_id,
      user_branch_id,
      user_branch_natural_id,
      create_timestamp,
      update_timestamp,
      ulid,
      construction_number,
      site_name,
      site_name_kana,
      site_prefecture,
      site_city,
      site_address,
      register_type,
      latitude,
      longitude
    )
    VALUES
    (
      _project_natural_id,                                                   -- �H��ID
      _customer_id,                                                          -- �ڋq�}�X�^�[�e�[�u��ID
      _customer_natural_id,                                                  -- �ڋqID
      _customer_branch_id,                                                   -- �ڋq�x�X�}�X�^�[�e�[�u��ID
      _customer_branch_natural_id,                                           -- �ڋq�x�XID
      _customer_user_id,                                                     -- �ڋq�S���҃}�X�^�[�e�[�u��ID
      _customer_user_natural_id,                                             -- �ڋq�S����ID
      1,                                                                     -- �V�X�e�����p�҃e�[�u��ID�F�ŏI�X�V��
      _branch_id,                                                            -- �����h�}�[�N�x�X�}�X�^�e�[�u��ID
      _branch_natural_id,                                                    -- �����h�}�[�N�x�XID
      CURRENT_TIMESTAMP,                                                     -- ���R�[�h�쐬��
      CURRENT_TIMESTAMP,                                                     -- ���R�[�h�X�V��
      getULID(),                                                             -- ULID
      new.construction_number,                                               -- �H���ԍ�
      new.site_name,                                                         -- ���ꖼ�^�@��
      new.site_name_kana,                                                    -- ���ꖼ�^�@���F�J�i
      new.site_prefecture,                                                   -- ���ꏊ�ݒn�F�s���{���R�[�h
      new.site_city,                                                         -- ���ꏊ�ݒn�F�s�撬��
      new.site_address,                                                      -- ���ꏊ�ݒn�F�Ԓn�i�����ȍ~�j
      1,                                                                     -- �f�[�^�o�^�Ҏ��
      new.latitude,                                                          -- �ܓx
      new.longitude                                                          -- �o�x
    );
  
    -- �A�gID���擾
    SET NEW.core_id = LAST_INSERT_ID();
  END IF;
END;


-- �g���K�[�F �H�����(�X�V) �X�V���F21/06/09
DROP TRIGGER IF EXISTS upd_cu_project;

CREATE TRIGGER upd_cu_project  BEFORE INSERT ON cu_project FOR EACH ROW
BEGIN

  -- �ڋq�V�X�e���ɂ��X�V�̏ꍇ
  IF new.update_system_type = 2 THEN

    UPDATE project 
    SET
        update_timestamp = CURRENT_TIMESTAMP
      , construction_number = new.construction_number
      , site_name = new.site_name
      , site_name_kana = new.site_name_kana
      , site_prefecture = new.site_prefecture
      , site_city = new.site_city
      , site_address = new.site_address
      , latitude = new.latitude
      , longitude = new.longitude
      , register_type = 1
      , lastupdate_user_id = 1
    WHERE
      id = new.core_id;

  END IF;
END;

-- �g���K�[�F ���ψ˗����(�o�^) �X�V���F21/06/10
DROP TRIGGER IF EXISTS ins_cu_request;

CREATE TRIGGER ins_cu_request  BEFORE INSERT ON cu_request FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id, _request_cnt INT;
  DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id, _request_natural_id  VARCHAR(255);

  -- �ڋq�V�X�e���ɂ��X�V�̏ꍇ
  IF new.create_system_type = 2 THEN
  
    -- ���ψ˗������̎擾�iID�����p�j
    SELECT COUNT(*)
    INTO _request_cnt
    FROM cu_request
    WHERE project_id = new.project_id 
      AND request_type = new.request_type;
    
    -- �H�����̎擾
    SELECT 
      p.id,
      p.project_natural_id
    INTO
      _project_id,
      _project_natural_id
    FROM project p
      INNER JOIN cu_project cp ON cp.core_id = p.id 
    WHERE cp.project_id = new.project_id;

    -- �˗�ID�̍쐬  
    SET _request_natural_id = CONCAT( _project_natural_id, '-C', new.request_type, LPAD( _request_cnt,3, '0' ));

    -- �ڋq�A�ڋq�x�X�A�ڋq�S���҂̎擾
    SELECT 
      c.customer_id, 
      c.customer_natural_id,
      c.customer_branch_id,
      c.customer_branch_natural_id,
      c.id,
      c.customer_user_natural_id
    INTO 
      _customer_id, 
      _customer_natural_id,
      _customer_branch_id,
      _customer_branch_natural_id,
      _customer_user_id,
      _customer_user_natural_id
    FROM customer_user c
      INNER JOIN cu_customer_user cc ON cc.core_id = c.id
      INNER JOIN cu_project cp ON cp.customer_user_id = cc.customer_user_id
    WHERE cp.project_id = new.project_id;

    -- �x�X�����擾
    SELECT 
      id,
      user_branch_natural_id
    INTO 
      _branch_id,
      _branch_natural_id
    FROM user_branch ub
      INNER JOIN cu_branch cb ON cb.core_id = ub.id
      INNER JOIN cu_branch_area a ON a.branch_id = cb.branch_id
      INNER JOIN cu_project p ON a.prefecture = p.site_prefecture
    WHERE p.project_id = new.project_id;
  
    -- �����˗����쐬
    INSERT INTO request( 
        create_timestamp
      , update_timestamp
      , ulid
      , request_natural_id
      , project_natural_id
      , customer_id
      , customer_natural_id
      , customer_branch_id
      , customer_branch_natural_id
      , customer_user_id
      , customer_user_natural_id
      , user_branch_natural_id
      , request_date
      , estimate_deadline
      , request_type
      , want_start_date
      , want_end_date
      , car_qty
      , light_truck_qty
      , truck_qty
      , other_car_qty
      , other_car_detail
      , request_other_deadline
      , request_other_start_date
      , request_other_end_date
      , request_other_qty
      , want_guide_type
      , cc_email
      , customer_other_request
      , survey_status
      , register_type
      , request_cancel_check_flag
      , lastupdate_user_id
      , project_id
      , user_branch_id
    ) VALUES (
        CURRENT_TIMESTAMP                                        -- ���R�[�h�쐬��
      , CURRENT_TIMESTAMP                                        -- ���R�[�h�X�V��
      , getULID()                                                -- ULID
      , _request_natural_id                                      -- �˗�ID
      , _project_natural_id                                      -- �H��ID
      , _customer_id                                             -- �ڋq��Ѓ}�X�^�[�e�[�u��ID
      , _customer_natural_id                                     -- �ڋq���ID
      , _customer_branch_id                                      -- �ڋq�x�X�}�X�^�[�e�[�u��ID
      , _customer_branch_natural_id                              -- �ڋq�x�XID
      , _customer_user_id                                        -- �ڋq�S���҃}�X�^�[�e�[�u��ID
      , _customer_user_natural_id                                -- �ڋq�S����ID
      , _branch_natural_id                                       -- �����h�}�[�N�x�XID
      , new.request_date                                         -- �˗���t��
      , IFNULL( new.response_request_date, getCalcBusinessDay( CURRENT_DATE, 4  ))  -- ���ϒ�o�����^�ڋq��]�������ꍇ��4�c�Ɠ���
      , new.request_type                                         -- �˗����
      , new.want_start_date                                      -- ���p���ԁF�J�n
      , new.want_end_date                                        -- ���p���ԁF�I��
      , new.car_qty                                              -- �䐔�F��p�ԁi�y�����ԁE�n�C�G�[�X���j 
      , new.light_truck_qty                                      -- �䐔�F�y�g���b�N
      , new.truck_qty                                            -- �䐔�F2���g���b�N
      , new.other_car_qty                                        -- �䐔�F���̑��i�v�j
      , new.other_car_detail                                     -- ���̑��ڍ�
      , new.request_other_deadline                               -- �������
      , new.request_other_start_date                             -- �_��J�n��
      , new.request_other_end_date                               -- �_��I����
      , new.request_other_qty                                    -- ��
      , new.want_guide_type                                      -- �ē����@
      , new.cc_email                                             -- �ڋq���w�肷��CC���[���A�h���X
      , new.customer_other_request                               -- �ڋq����̗v�]��
      , 0                                                        -- �����X�e�[�^�X �i0:�������j
      , 1                                                        -- �f�[�^�o�^���[�U�[��ʁi1:�ڋq�o�^�f�[�^)
      , 0                                                        -- �˗��L�����Z���m�F�t���O�i0:���m�F�j
      , 1                                                        -- �V�X�e�����p�҃e�[�u��ID�F�ŏI�X�V��
      , _project_id                                              -- �H���}�X�^�[�e�[�u��ID
      , _branch_id                                               -- �����h�}�[�N�x�X�}�X�^�[�e�[�u��ID
    );

    -- �A�gID���擾
    SET NEW.core_id = LAST_INSERT_ID();

    -- �˗���ʂ��̑��̏ꍇ�̌㑱����

  END IF;
END;

-- �g���K�[�F ���ψ˗����(�X�V) �X�V���F21/06/09
DROP TRIGGER IF EXISTS upd_cu_request;

CREATE TRIGGER upd_cu_request  AFTER UPDATE ON cu_request FOR EACH ROW
BEGIN

  -- �ڋq�V�X�e���ɂ��X�V�̏ꍇ
  IF new.update_system_type = 2 THEN
  
    -- ���ψ˗��X�e�[�^�X���������AWeb�����A���n�����܂ł��X�V�\�Ƃ���
    IF old.request_status IN ( 0, 1, 2 )  THEN
  
      UPDATE request
        SET 
          update_timestamp                = CURRENT_TIMESTAMP    -- ���R�[�h�X�V��
        , want_start_date                 = new.want_start_date  -- ���p���ԁF�J�n
        , want_end_date                   = new.want_end_date    -- ���p���ԁF�I��
        , car_qty                         = new.car_qty
        , light_truck_qty                 = new.light_truck_qty
        , truck_qty                       = new.truck_qty
        , other_car_qty                   = new.other_car_qty
        , other_car_detail                = new.other_car_detail
        , request_other_deadline          = new.request_other_deadline
        , request_other_start_date        = new.request_other_start_date
        , request_other_end_date          = new.request_other_end_date
        , request_other_qty               = new.request_other_qty
        , want_guide_type                 = new.want_guide_type
        , cc_email                        = new.cc_email
        , customer_other_request          = new.customer_other_request
        , register_type                   = 1
        , request_cancel_date             =
           CASE
             WHEN old.request_status IN ( 0, 1, 2 ) AND new.request_status = 9 THEN CURRENT_DATE
             ELSE NULL
           END                                                 -- �����r���I���ƂȂ����ꍇ�A�L�����Z�����t������
        , lastupdate_user_id             = 1
       WHERE id = new.core_id;

    END IF;
  END IF;
END;


-- �g���K�[�F ���ψ˗��Ώے��ԏ�i�o�^�j �X�V���F21/06/09
DROP TRIGGER IF EXISTS ins_cu_request_parking;

CREATE TRIGGER ins_cu_request_parking  AFTER INSERT ON cu_request_parking FOR EACH ROW
BEGIN

  DECLARE _parking_id, _request_id, _project_id, _supplier_id, _user_branch_id, _extend_estimate_id
        , _customer_id, _customer_branch_id, _customer_user_id INT;
  DECLARE _project_natural_id, _request_natural_id, supplier_natural_id,  _user_branch_natural_id, _extend_estimate_natural_id
        , _customer_natural_id, _customer_branch_natural_id, _customer_user_natural_id VARCHAR(255);

  -- ���ԏ���̎擾
  SELECT cp.core_id, p.supplier_id, p.supplier_natural_id
  INTO _parking_id, _supplier_id, supplier_natural_id
  FROM cu_parking cp
    INNER JOIN parking p ON p.id = cp.core_id
  WHERE parking_id = new.parking_id;
  
  -- �H�����̎擾
  SELECT 
      r.project_id, 
      r.project_natural_id,
      r.id,
      r.request_natural_id, 
      r.customer_id,
      r.customer_natural_id,
      r.customer_branch_id,
      r.customer_branch_natural_id,
      r.customer_user_id,
      r.customer_user_natural_id,
      r.user_branch_id,
      r.user_branch_natural_id,
      cr.extend_estimate_id
  INTO 
    _project_id,
    _project_natural_id,
    _request_id,
    _request_natural_id,
    _customer_id,
    _customer_natural_id,
    _customer_branch_id,
    _customer_branch_natural_id,
    _customer_user_id,
    _customer_user_natural_id,
    _user_branch_id,
    _user_branch_natural_id,
    _extend_estimate_id
  FROM cu_request cr
    INNER JOIN request r ON r.id = cr.core_id
  WHERE cr.request_id = new.request_id;
  
  -- ����������ID �̎擾
  IF _extend_estimate_id IS NOT NULL THEN

    SELECT e.estimate_natural_id
    INTO _extend_estimate_natural_id
    FROM cu_estimate ce
      INNER JOIN estimate ON e.core_id = ce.estimate_id
    WHERE estimate_id = _extend_estimate_id;

  END IF;
  
  -- �˗����ԏ�Ǘ��̓o�^
  INSERT INTO request_parking( 
      create_timestamp
    , update_timestamp
    , ulid
    , project_id
    , project_natural_id
    , request_id
    , request_natural_id
    , parking_id
    , customer_id
    , customer_natural_id
    , customer_branch_id
    , customer_branch_natural_id
    , customer_user_id
    , customer_user_natural_id
    , supplier_id
    , supplier_natural_id
    , user_branch_id
    , user_branch_natural_id
    , request_parking_status
    , route_map_parking_flag
    , extend_estimate_natural_id
    , lastupdate_user_id
    , want_parking_flag
  ) 
  VALUES (
      CURRENT_TIMESTAMP
    , CURRENT_TIMESTAMP
    , getULID()
    , _project_id
    , _project_natural_id
    , _request_id
    , _request_natural_id
    , _parking_id
    , _customer_id
    , _customer_natural_id
    , _customer_branch_id
    , _customer_branch_natural_id
    , _customer_user_id
    , _customer_user_natural_id
    , _supplier_id
    , _supplier_natural_id
    , _user_branch_id
    , _user_branch_natural_id
    , 0
    , 1
    , _extend_estimate_natural_id
    , 1
    , 1
   );

END;


-- �g���K�[�F ���ρi�X�V�j �X�V���F21/06/09
DROP TRIGGER IF EXISTS upd_cu_estimate;

CREATE TRIGGER upd_cu_estimate  AFTER UPDATE ON cu_estimate FOR EACH ROW
BEGIN

  -- �ڋq�����V�X�e���ōX�V�����ꍇ
  IF update_system_type = 2 THEN


    -- ��������
    -- ����̍X�V�Ώۂ����߂Ċm�F
    
    -- ��������
    IF new.estimate_status = 7 THEN
      UPDATE estimate
         SET 
           estimate_status = new.estimate_status,
           update_timestamp = CURRENT_TIMESTAMP,
           lastupdate_user_id = 1
       WHERE id = new.core_id;
    END IF;
    
    
  END IF;
END;


-- �g���K�[�F �_��i�X�V�j2021/06/10
-- �_�񎞂Ɋ�����X�V���鏈���Ȃ�
-- �ڋq�V�X�e�����݂̂ŃX�e�[�^�X�Ǘ�

-- ���[�U�[���i�X�V�j
DROP TRIGGER IF EXISTS upd_cu_user;

CREATE TRIGGER upd_cu_user  BEFORE UPDATE ON cu_user FOR EACH ROW
BEGIN

  -- �ڋq�����V�X�e���ōX�V�����ꍇ
  IF new.update_system_type = 2 THEN

    -- �ڋq�S���ҏ��̍X�V
    UPDATE customer_user cu 
      INNER JOIN cu_customer_user ccu on ccu.core_id = cu.id  
      INNER JOIN cu_user_branch cub ON cub.customer_user_id = ccu.customer_user_id       
      SET 
      cu.lastupdate_user_id              = 1
     ,cu.update_timestamp                = CURRENT_TIMESTAMP
     ,cu.customer_user_name              = new.customer_user_name
     ,cu.customer_user_name_kana         = new.customer_user_name_kana
     ,cu.customer_user_email             = new.login_id
     ,cu.customer_user_tel               = new.customer_user_tel
     ,cu.customer_reminder_sms_flag      = new.customer_reminder_sms_flag
    WHERE cub.user_id = new.user_id;
    
  END IF;

END;

-- ���[�U�[�����x�X�i�o�^�j
DROP TRIGGER IF EXISTS ins_cu_user_branch;

CREATE TRIGGER ins_cu_user_branch  BEFORE UPDATE ON cu_user_branch FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _cu_customer_user_id INT;
  DECLARE _customer_natural_id, _customer_branch_natural_id, _customer_user_natural_id
        , _customer_user_name, _customer_user_name_kana VARCHAR(255) ;
  DECLARE _login_id VARCHAR(2048);
  DECLARE _customer_reminder_sms_flag BOOL;
  DECLARE _customer_user_tel VARCHAR(13);

  -- �ڋq���̎擾
  SELECT cb.customer_id, cb.customer_natural_id, cb.id, cb.customer_branch_natural_id
  INTO _customer_id, _customer_natural_id, _customer_branch_id, _customer_branch_natural_id
  FROM cu_customer_branch ccb
    INNER JOIN customer_branch cb ON cb.id = ccb.core_id
  WHERE ccb.customer_branch_id = new.customer_branch_id;
  
  -- ���[�U�[���̎擾
  SELECT login_id,  customer_user_name,  customer_user_name_kana,  customer_reminder_sms_flag,  customer_user_tel
  INTO  _login_id, _customer_user_name, _customer_user_name_kana, _customer_reminder_sms_flag, _customer_user_tel
  FROM cu_user
  WHERE user_id = new.user_id;

  -- �ڋq�S���҃}�X�^�ɑ��݊m�F
  SELECT customer_user_id, customer_user_natural_id
  INTO _customer_user_id, _customer_user_natural_id
  FROM customer_user
  WHERE customer_id = _customer_id
    AND customer_branch_id = _customer_branch_id
    AND customer_user_email = _login_id
  LIMIT 1;

  -- �f�[�^�����݂��Ȃ��ꍇ�A
  IF _customer_id IS NULL THEN

    -- �ڋq�x�X�S���҂��쐬
    INSERT INTO customer_user (
      customer_id
     ,customer_natural_id
     ,customer_branch_id
     ,customer_branch_natural_id
     ,customer_user_natural_id
     ,lastupdate_user_id
     ,ulid
     ,create_timestamp
     ,update_timestamp
     ,customer_user_name
     ,customer_user_name_kana
     ,customer_user_email
     ,customer_user_tel
     ,customer_reminder_sms_flag
    )
    VALUES (
      _customer_id
     ,_customer_natural_id
     ,_customer_branch_id
     ,_customer_branch_natural_id
     ,_customer_user_natural_id
     ,1
     ,getULID()
     ,CURRENT_TIMESTAMP
     ,CURRENT_TIMESTAMP
     ,_customer_user_name
     ,_customer_user_name_kana
     ,_customer_user_email
     ,_customer_user_tel
     ,_customer_reminder_sms_flag    
    );

    SELECT customer_user_id
      INTO _cu_customer_user_id
      FROM cu_customer_user ccu
     WHERE ccu.core_id = LAST_INSERT_ID();  

  ELSE
  -- �f�[�^�����݂����ꍇ
  -- �����I��1���݂̂��擾����
  -- ����x�X�œ��ꃁ�[���A�h���X�𗘗p�ł��郆�[�U�[��1���܂�
    SELECT customer_user_id
      INTO _cu_customer_user_id
      FROM cu_customer_user ccu
     WHERE ccu.core_id = _customer_user_id;  
  
  END IF;
  
  -- �ڋq�S����ID���Z�b�g
  SET new.customer_user_id = _cu_customer_user_id;
  
END;

import React, { useState } from "react";
import { Container } from "react-bootstrap";
import { useTranslation } from "react-i18next";
import { FormProvider, useForm } from "react-hook-form";
import Postal from "japan-postal-code";
import { useHistory } from "react-router-dom";
import LinkName from "../../constants/link_name";
import HeaderNew from "../layouts/header_new";
import FooterNew from "../layouts/footer_new";
import "../../assets/scss/screens/construction_info.scss";
import ConstructionInfoInput from "./components/form";

export default function ConstructionInfo(props) {
  const [t] = useTranslation();
  const history = useHistory();
  const [isBtnSearchZip, setIsBtnSearchZip] = useState(false);
  const [isDisabled, setIsDisabled] = useState(false);
  const [isBtnSubmit] = useState(false);

  const methods = useForm({
    mode: "onChange",
    reValidateMode: "onChange",
    defaultValues: {
      construction_number: "",
      construction_name: "",
      zipcode: "",
      site_prefecture: "",
      site_city: "",
      site_address: "",
    },
  });
  const { handleSubmit, setValue, watch } = methods;

  const onSubmit = (data) => {
    console.log(data);
  };

  const onClick = () => {
    history.push(LinkName.LOGIN);
  };

  const handleChecked = () => {
    setIsDisabled(!isDisabled);
    setIsBtnSearchZip(!isBtnSearchZip);
    setValue("site_prefecture", "");
    setValue("site_city", "");
  };

  const zipcode = watch("zipcode");

  const handleZipcode = (e) => {
    if (zipcode) {
      Postal.get(zipcode, function (address) {
        setValue("site_prefecture", address.prefecture);
        setValue("site_city", address.city);
      });
    }
    e.preventDefault();
  };

  return (
    <>
      <HeaderNew />
      <div className="sticky-footer">
        <div className="page-template">
          <Container>
            <div className="page-content">
              <div className="content-wrapper">
                <div className="construction-information create">
                  <div className="card">
                    <div className="heading">
                      <h1 className="title-page">
                        {t("WEG_03_0010_sub_title_page")}
                      </h1>
                    </div>
                    <FormProvider {...methods}>
                      <form
                        className="form-construction-information"
                        onSubmit={handleSubmit(onSubmit)}
                      >
                        <ConstructionInfoInput
                          isBtnSearchZip={!isBtnSearchZip}
                          handleZipcode= {handleZipcode}
                          handleChecked={handleChecked}
                          isDisabled={!isDisabled}
                        />
                        <div className="map">
                          <div className="d-block flex-wrap align-items-center form-group">
                            <div className="form-group__control mt-5">
                              <button
                                className="btn btn-pink btn-sm"
                                disabled={isBtnSubmit}
                              >
                                {t("WEG_03_0010_btn_search_address")}
                              </button>
                              <p>
                                地図上のピンを操作して、工事現場の出入り口付近にピンを合わせてください。
                              </p>
                            </div>
                          </div>
                        </div>
                        <div className="text-center card-footer">
                          <button
                            className="btn-lg btn-cancel  btn btn-primary"
                            onClick={onClick}
                          >
                            <strong>{t("WEG_03_0010_btn_cancel")}</strong>
                          </button>
                        </div>
                      </form>
                    </FormProvider>
                  </div>
                </div>
              </div>
            </div>
          </Container>
        </div>
      </div>
      <FooterNew />
    </>
  );
}

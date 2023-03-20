import React, { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { Container } from "react-bootstrap";
import { useHistory } from "react-router-dom";
import detailConstructionInfo from "../../api/detailConstructionInfo";
import LinkName from "../../constants/link_name";
import HeaderNew from "../layouts/header_new";
import FooterNew from "../layouts/footer_new";
import "../../assets/scss/screens/detail_construction_info.scss";
import DetailConstructionInfoInput from "./components/form";

export default function DetailConstructionInfoController() {
  const [t] = useTranslation();
  const history = useHistory();
  const [info, setInfo] = useState(null);

  const onClick = () => {
    history.push(LinkName.LOGIN);
  };

  useEffect(() => {
    detailConstructionInfo.getDetailConstructionInfo().then(
      (response) => {
        setInfo(response.data);
      },
      (error) => {
        console.log(error);
      }
    );
  }, []);

  return (
    <>
      <HeaderNew />
      <div className="sticky-footer">
        <div className="page-template">
          <Container>
            <div className="wrapper">
              <div className="template-detail-construction-info">
                <div className="action-box">
                  <button className="btn btn-primary btn-change">
                    {t("WEG_03_0011_btn_change_construction")}
                  </button>
                </div>
                <DetailConstructionInfoInput info={info}/>
                <div className="text-center card-footer">
                  <button
                    className="btn btn-primary btn-cancel"
                    onClick={onClick}
                  >
                    {t("WEG_03_0011_text_return")}
                  </button>
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

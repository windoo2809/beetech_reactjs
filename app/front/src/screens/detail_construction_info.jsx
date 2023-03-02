import React, { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { Container } from "react-bootstrap";
import { useHistory } from "react-router-dom";
import detailConstructionInfo from "../api/detailConstructionInfo";
import LinkName from "../constants/link_name";
import HeaderNew from "./layouts/header_new";
import FooterNew from "./layouts/footer_new";
import "../assets/scss/screens/detail_construction_info.scss";

export default function DetailConstructionInfo() {
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
                <div className="card-content">
                  <table>
                    <thead>
                      <tr>
                        <th> {t("WEG_03_0010_label_construction_number")}</th>
                      </tr>
                      <tr>
                        <th> {t("WEG_03_0010_label_construction_name")}</th>
                      </tr>
                      <tr>
                        <th> {t("WEG_03_0010_label_post_code")}</th>
                      </tr>
                      <tr>
                        <th> {t("WEG_03_0010_label_prefectures")}</th>
                      </tr>
                      <tr>
                        <th> {t("WEG_01_0100_label_city")}</th>
                      </tr>
                      <tr>
                        <th> {t("WEG_03_0010_label_address")}</th>
                      </tr>
                    </thead>
                    {info && (
                      <tbody>
                        <tr>
                          <td>{info.data.id}</td>
                        </tr>
                        <tr>
                          <td>{info.data.email}</td>
                        </tr>
                        <tr>
                          <td>{info.data.first_name}</td>
                        </tr>
                        <tr>
                          <td>{info.data.last_name}</td>
                        </tr>
                        <tr>
                          <td>{info.data.email}</td>
                        </tr>
                        <tr>
                          <td>{info.data.email}</td>
                        </tr>
                      </tbody>
                    )}
                    <div className="content">
                      <div className="map">
                        <span>{t("WEG_03_0010_label_map")}</span>
                      </div>
                    </div>
                  </table>
                  {/* <div className="content">
                      <span className="name">
                        {t("WEG_03_0010_label_construction_number")}
                      </span>
                      <span>{info.data.email}</span>
                    </div>
                    <div className="content">
                      <span className="name">
                        {t("WEG_03_0010_label_construction_name")}
                      </span>
                      <span>{}</span>
                    </div>
                    <div className="content">
                      <span className="name">
                        {t("WEG_03_0010_label_post_code")}
                      </span>
                      <span>{info.data.first_name}</span>
                    </div>
                    <div className="content">
                      <span className="name">
                        {t("WEG_03_0010_label_prefectures")}
                      </span>
                      <span>{info.data.last_name}</span>
                    </div>
                    <div className="content">
                      <span className="name">
                        {t("WEG_01_0100_label_city")}
                      </span>
                      <span>{info.data.last_name}</span>
                    </div>
                    <div className="content">
                      <span className="name">
                        {t("WEG_03_0010_label_address")}
                      </span>
                      <span>{info.data.last_name}</span>
                    </div>
                    <div className="content">
                      <div className="map">
                        <span>{t("WEG_03_0010_label_map")}</span>
                      </div>
                    </div> */}
                </div>
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

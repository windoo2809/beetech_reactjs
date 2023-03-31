import React from "react";
import { useTranslation } from "react-i18next";

export default function ListConstructionInfoInput(props) {
  const [t] = useTranslation();
  const {
    isActive,
    setIsActive,
    plus,
    minus,
    listIconEstimate,
    listEstimate,
    listLabelEstimate,
    isBtn,
  } = props;

  return (
    <div className="accordion-item">
      <div className="accordion-heading">
        <span>{t("現場名")}</span>
        <button className="icon" onClick={() => setIsActive(isActive)}>
          {isActive ? <img src={minus} alt="" /> : <img src={plus} alt="" />}
        </button>
      </div>

      {isActive && (
        <>
          <div className="accordion-title">
            <span>{t("工事情報：工事番号, 現場名,現場住所")}</span>
          </div>
          <div className="accordion-content">
            <label className="status">
              <span>{t("契約ス")}</span>
            </label>
            <div className="content">
              <div className="info">
                <label>
                  {t("WEG_03_0002_request_estimate")}:<a href="#a">00001</a>
                </label>
                <label>
                  {t("WEG_03_0002_estimate_id")}: <span>b</span>
                </label>
                <label>
                  {t("種別")}: <span>b</span>
                </label>
                <label>
                  {t("見積情報")}: <span>b</span>
                </label>
                <label>
                  {t("契約情報")}: <span>b</span>
                </label>
              </div>
              <div className="estimate-bar">
                <div className="icon-estimate">{listIconEstimate()}</div>
                <div className="estimate">{listEstimate()}</div>
                <div className="label-estimate">{listLabelEstimate()}</div>
                <div className="date">{t("契約開始日")}</div>
              </div>
              <div className="action-box">
                {isBtn && (
                  <>
                    <label>({t("入金")})</label>
                    <button type="submit" className="btn btn-primary">
                      {t("WEG_03_0002_extension")}
                    </button>
                  </>
                )}
              </div>
            </div>
          </div>
          <hr className="line-dashed" />
        </>
      )}
    </div>
  );
}

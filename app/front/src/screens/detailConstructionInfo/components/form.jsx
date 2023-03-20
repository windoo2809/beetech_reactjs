import React from "react";
import { useTranslation } from "react-i18next";

export default function DetailConstructionInfoInput(props) {
  const [t] = useTranslation();

  return (
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
        {props.info && (
          <tbody>
            <tr>
              <td>{props.info.data.id}</td>
            </tr>
            <tr>
              <td>{props.info.data.email}</td>
            </tr>
            <tr>
              <td>{props.info.data.first_name}</td>
            </tr>
            <tr>
              <td>{props.info.data.last_name}</td>
            </tr>
            <tr>
              <td>{props.info.data.email}</td>
            </tr>
            <tr>
              <td>{props.info.data.email}</td>
            </tr>
          </tbody>
        )}
      </table>
      <div className="content">
        <div className="map">
          <span>{t("WEG_03_0010_label_map")}</span>
        </div>
      </div>
    </div>
  );
}

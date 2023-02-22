import React from "react";
import MainLayout from "./layouts/main_layout";
import { useTranslation } from "react-i18next";
import LinkName from "../constants/link_name";
import { Link } from "react-router-dom";

function StaticNotFound(props) {
  const [t] = useTranslation();

  return (
    <MainLayout>
      <div className="alert alert-light text-center p-0">
        <h2 className="mb-4">{t("pageNotFound")}</h2>

        <Link to={LinkName.TOP} className="btn btn-primary btn-notFound">
          {t("pageNotFoundGoBackTop")}
        </Link>
      </div>
    </MainLayout>
  );
}

export default StaticNotFound;

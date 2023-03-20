import React, { lazy, Suspense, useState } from "react";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import ProtectedRoute from "./auth/protected_route";
import PageLoader from "./components/page_loader";
import LinkName from "./constants/link_name";
import AppContext from "./context/appContext";
const Login = lazy(() => import("./screens/login/login"));
const FormMail = lazy(() => import("./screens/formMail/form_mail"));
const StaticNotFound = lazy(() => import("./screens/notfound"));
const ListConstruction = lazy(() => import("./screens/listConstructioninfo/list_construction_info"));
const ConstructionInfo = lazy(() => import("./screens/constructionInfo/construction_info"));
const DetailConstructionInfo = lazy(() => import("./screens/detailConstructionInfo/detail_construction_info"));
const Dashboard = lazy(() => import("./screens/dashboard.jsx"));
const ParkingLot = lazy(() => import("./screens/parkingLot/parking_lot"));

localStorage.setItem("i18nextLng", "jp");

function App() {
  const [appState, setAppState] = useState({
    loginInfo: null,
  });

  const setAppStateByKey = (obj) => {
    let dataTmp = { ...appState };
    Object.entries(obj).forEach(([key, value]) => {
      dataTmp[key] = value;
    });

    setAppState(dataTmp);
  };

  const appStateInfo = {
    appState,
    setAppStateByKey,
  };

  return (
    <BrowserRouter>
      <AppContext.Provider value={appStateInfo}>
        <Suspense fallback={<PageLoader />}>
          <Switch>
            <Route exact path={LinkName.PAGE_LOGIN} component={Login} />
            <Route exact path={LinkName.LOGIN} component={Login} />
            <Route exact path={LinkName.MAIL_FORM} component={FormMail} />
            <Route exact path={LinkName.LIST_CONSTRUCTION} component={ListConstruction} />
            <Route exact path={LinkName.CONSTRUCTION_INFO} component={ConstructionInfo} />
            <Route exact path={LinkName.DETAIL_CONSTRUCTION_INFO} component={DetailConstructionInfo} />
            <Route exact path={LinkName.PARKING_LOT} component={ParkingLot} />
            <Route exact path={LinkName.DASHBOARD} component={Dashboard} />
            <ProtectedRoute component={StaticNotFound} />
          </Switch>
        </Suspense>
      </AppContext.Provider>
    </BrowserRouter>
  );
}

export default App;

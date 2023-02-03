import React from "react";
import { Modal } from "reactstrap";
import {Link, withRouter} from "react-router-dom";
import {useTranslation } from "react-i18next";
import { replaceString } from '../../helpers/helpers';
import LinkName from "../../constants/link_name";
import "../../assets/scss/screens/modal.scss";

function DialogError(props){
    const [ t ] = useTranslation();
    return(
        <Modal 
            isOpen={props.modal} 
            className="dialog_error"
        >
            <div className="text-center">
                {
                    !props.codeError && props.text
                }
                {
                    props.codeError && <> {replaceString(t('CMN0006-W'),[props.text])}</>
                }
                
            </div>
            <div className="text-center box-modal-action">
                <Link to={props.parentPage ? {
                    pathname: props.parentPage.pathname
                } : {
                    pathname: LinkName.TOP,
                }} className="btn btn-lg btn-primary">OK</Link>
            </div>
        </Modal>
    );
}

DialogError.defaultProps = {
    codeError: true
};
export default withRouter(DialogError);

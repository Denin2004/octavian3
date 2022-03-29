import React, {Component} from 'react';

import { Button } from 'antd';

import { withTranslation } from 'react-i18next';
import { faFileExcel } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import useWithForm from '@app/web/mfw/mfwForm/MfwFormHOC';

class MfwExcel extends Component {
    
    constructor(props) {
        super(props);
        this.excel = this.excel.bind(this);
    }

    componentDidMount() {
        this.refExcel = React.createRef();
    }
    
    excel() {
        var data = this.props.data();
        if (data.head != false) {
           var t = document.createElement('template');
           t.innerHTML = data.head;
           var head = t.content.firstChild,
               sorters = head.getElementsByClassName('ant-table-column-sorter'),
               noExcel = head.getElementsByClassName('mfw-noexcel'); 
            while (sorters.length > 0) {
                sorters[0].remove();
            }
            while (noExcel.length > 0) {
                noExcel[0].remove();
            }
            data.head = head.outerHTML;
            data.headLines = head.children.length;
        }
        this.refExcel.current.value = JSON.stringify(data);
    }
    
    render() {
        return <form form={this.props.form}
          method="post"
          action={window.mfwApp.urls.grid.excel}
          target="_blank">
            <input ref={this.refExcel} type="hidden" name="excelData" className="mfw-excel-data"/>
            <Button htmlType="submit" onClick={this.excel}><FontAwesomeIcon icon={faFileExcel}/></Button>
        </form>
    }
}

export default withTranslation()(useWithForm(MfwExcel));
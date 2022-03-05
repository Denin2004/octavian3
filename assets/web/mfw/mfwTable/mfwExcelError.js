import React, {Component} from 'react';
import { withTranslation } from 'react-i18next';
import { Result } from 'antd';
import { CloseCircleOutlined } from '@ant-design/icons';


class MfwExcelError extends Component {
    render() {
        return <Result
          status="error"
          title={this.props.t('grid.errors.excel')}
          subTitle="Please check and modify the following information before resubmitting."/>
    }
}

export default withTranslation()(MfwExcelError);
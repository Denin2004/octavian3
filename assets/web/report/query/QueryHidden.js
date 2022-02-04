import React, {Component} from 'react';

import { Form, Input } from 'antd';

import { withTranslation } from 'react-i18next';

class QueryHidden extends Component {
    
    render() {
        return (
            <Form.Item name={this.props.field.name}
              hidden={true}
              initialValue={this.props.field.value}>
                <Input/>
            </Form.Item>
        )
    }
}

export default withTranslation()(QueryHidden);
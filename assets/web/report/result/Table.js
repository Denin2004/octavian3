import React, {Component} from 'react';

import ResizeObserver from 'rc-resize-observer';
import axios from 'axios';

import MfwTable from '@app/web/mfw/mfwTable/mfwMain';

class TableResult extends Component {
    constructor(props){
        super(props);
        this.state = {loading: true}
    }

    render() {
        return (
            <MfwTable
                mfwConfig={this.props.tableConfig}
                mfwData={this.props.data}
                loading={this.props.loading}
                scroll={{
                    y: 300,
                    x: '100vw'
                }}
            />
        )
    }
}

export default TableResult;
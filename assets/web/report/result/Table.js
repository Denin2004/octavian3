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
            <div className="flex">
                <div className="header">Верхний блок</div>
                    <MfwTable
                        mfwColumns={this.props.tableConfig.tableInit.columns}
                        mfwData={this.props.data}
                        multiSelect={true}
                        colGroup={['CURRENCY_ABBR']}
                        loading={this.props.loading}
                        scroll={{
                            y: 300,
                            x: '100vw'
                        }}
                    />
                <div className="footer">Нижний блок</div>
            </div>
        )
    }
}

export default TableResult;
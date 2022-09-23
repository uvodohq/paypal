import { Loader, styled } from '@uvodohq/planum'
import * as React from 'react'
const LoadingContent = styled('div', {
    position: 'absolute',
    inset: 0,
    dflex: 'center',
    zIndex: 9,
    // backgroundColor: 'rgba(0, 0, 0, 0.01)',
    p: 4,
    m: -4,
})
const Wrapper = styled('div', {
    // filter: `grayscale(0.5)`,
    disableActions: true,
    position: 'relative',
    variants: {
        isError: {
            true: {
                filter: `grayscale(1)`,
                [`& ${LoadingContent}`]: {
                    backgroundColor: 'rgba(0, 0, 0, 0.01)',
                },
            },
        },
    },
})
interface OverlayLoaderProps {
    isLoading: boolean
    isError?: boolean
    showSpinner?: boolean
    children: React.ReactNode
}
const WithLoadingWrapper = (props: OverlayLoaderProps) => (
    <Wrapper aria-live="polite" aria-busy="true" isError={props.isError}>
    {props.children}
    <LoadingContent>
    {props.showSpinner && <Loader size={'medium'} />}
</LoadingContent>
</Wrapper>
)
/**
 * @description
 *
 * OverlayLoader is for showing loading state and disables user to interact with busy/stale content
 * used for search filters, form submits .etc
 */
export function OverlayLoader(props: OverlayLoaderProps) {
    const { isLoading, isError, children } = props
    const content =
        isLoading || isError ? <WithLoadingWrapper {...props} /> : children
    return <>{content}</>
}

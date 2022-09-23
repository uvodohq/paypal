import {
    Box,
    Button,
    Input,
    InputPassword,
    Stack,
    toast,
} from "@uvodohq/planum"
import {useEffect, useState} from "react"
import {OverlayLoader, PageDescription} from "./components"
import http from "./config/http"

function App() {
    const [paypal_client_id, setPaypalClientId] = useState("")
    const [paypal_app_secret, setPaypalSecret] = useState("")
    const [paypal_sandbox_mode, setSandboxMode] = useState("")

    const [loading, setLoading] = useState(false)
    const [errors, setErrors] = useState(undefined)
    const [message, setMessage] = useState(undefined)

    useEffect(() => {
        setLoading(true)
        http.get("/plugins/uvodohq/paypal/settings").then(
            (res) => {
                setPaypalClientId(res.paypal_client_id)
                setPaypalSecret(res.paypal_app_secret)
                setSandboxMode(res.paypal_sandbox_mode)

                setLoading(false)
            },
            (err) => {
                setLoading(false)
                console.log(err)
                setErrors(undefined)
                setMessage("Oops. Something went wrong")
            }
        )
    }, [])

    useEffect(() => {
        if (message && errors !== undefined) {
            toast(message)
        }
    }, [message])

    const handleSave = () => {
        setLoading(true)
        setErrors(undefined)
        setMessage(undefined)

        http
            .post("/plugins/uvodohq/paypal/settings", {
                paypal_client_id,
                paypal_app_secret,
                paypal_sandbox_mode,
            })
            .then(
                (res) => {
                    setLoading(false)
                    toast("Plugin settings successfully updated")
                },
                (err) => {
                    if (err.response.data && err.response.data.errors) {
                        setErrors(err.response.data.errors)
                    }
                    setLoading(false)
                    toast("Oops. Something went wrong")
                }
            )
    }

    function getErrorMessage(selected_field) {
        let message = null

        errors &&
        errors.map((err) => {
            if (err.field === selected_field) {
                message = err.title
            }
        })

        return message
    }

    return (
        <Box
            css={{
                px: "$16",
                "@tablet": {
                    maxWidth: 504,
                    px: 0,
                    py: 2
                },
            }}
        >
            <PageDescription
                title="Paypal plugin"
                description="Paypal is payment gateway plugin. It allows you to accept payments from your customers with paypal."
            />
            <OverlayLoader isLoading={loading} showSpinner>
                <Stack y={24}>
                    <Input
                        aria-label="label"
                        label={"Client ID"}
                        placeholder="CLIENT ID"
                        value={paypal_client_id}
                        onChange={setPaypalClientId}
                        status={getErrorMessage("paypal_client_id") ? "error" : "normal"}
                        errorMessage={getErrorMessage("paypal_client_id")}
                    />

                    <Input
                        aria-label="label"
                        label={"App Secret"}
                        placeholder="APP SECRET"
                        value={paypal_app_secret}
                        onChange={setPaypalSecret}
                        status={getErrorMessage("paypal_app_secret") ? "error" : "normal"}
                        errorMessage={getErrorMessage("paypal_app_secret")}
                    />

                    <Button
                        onClick={handleSave}
                        isLoading={loading}
                        css={{
                            float: "right",
                        }}
                    >
                        Save changes
                    </Button>
                </Stack>
            </OverlayLoader>
        </Box>
    )
}

export default App
